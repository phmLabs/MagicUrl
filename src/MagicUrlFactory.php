<?php

namespace phmLabs\MagicUrl;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Uri;
use phm\HttpWebdriverClient\Http\Client\Decorator\CacheDecorator;
use phm\HttpWebdriverClient\Http\Client\Decorator\FileCacheDecorator;
use phm\HttpWebdriverClient\Http\Client\Guzzle\GuzzleClient;
use phmLabs\MagicUrl\Rule\DateRule;
use phmLabs\MagicUrl\Rule\FromRule;
use phmLabs\MagicUrl\Rule\ResolveException;
use phmLabs\MagicUrl\Rule\Rule;

class MagicUrlFactory
{
    const PREFIX = '@';

    const MAX_RULES = 20;

    const RULE_OPEN = '{';
    const RULE_CLOSE = '}';

    const REGEX_PATTERN = '(?=\{((?:[^{}]++|\{(?1)\})++)\})';

    /**
     * @var Rule[]
     */
    private $rules = [];

    /**
     * Attach a new rule to the factory
     *
     * @param Rule $rule
     */
    public function attachRule(Rule $rule)
    {
        $this->rules[] = $rule;
    }

    /**
     * @param $urlString
     * @return Uri
     * @throws ResolveException
     */
    public function resolve($urlString)
    {
        $urlString = trim($urlString);

        if (strpos($urlString, self::PREFIX) !== 0) {
            return new Uri($urlString);
        }

        if (substr_count($urlString, self::RULE_OPEN) != substr_count($urlString, self::RULE_CLOSE)) {
            throw new ResolveException('Opening and closing parenthesis are not the same number.');
        }

        $result = substr($urlString, strlen(self::PREFIX));


        $resolvedUrl = $this->resolveRules($result);

        if (!filter_var($resolvedUrl, FILTER_VALIDATE_URL)) {
            throw new ResolveException('The final resolved url string is not a valid url (' . substr($resolvedUrl, 0, 80) . ').');
        }

        return new Uri($resolvedUrl);
    }

    private function resolvePart($part)
    {
        $initialPart = $part;

        try {
            foreach ($this->rules as $rule) {
                $part = $rule->resolve($part);
            }

            if ($part === $initialPart) {
                throw new ResolveException('Unable to resolve {' . $part . '}, no matching rule found.');
            }
        } catch (ResolveException $e) {
            throw new ResolveException('Unable to resolve ' . $part . ' with message: ' . $e->getMessage());
        }

        return $part;
    }

    private function resolveRules($urlString)
    {
        $processedString = $urlString;

        $count = 0;

        while (($rule = $this->getInnerRule($processedString)) && $count < self::MAX_RULES) {
            $count++;
            $resolvedString = $this->resolvePart($rule);
            $processedString = str_replace(self::RULE_OPEN . $rule . self::RULE_CLOSE, $resolvedString, $processedString);
        }

        return $processedString;
    }

    private function getInnerRule($urlString)
    {
        preg_match_all('@' . self::REGEX_PATTERN . '@', $urlString, $parts);

        foreach ($parts[1] as $part) {
            if (strpos($part, self::RULE_OPEN) === false) {
                return $part;
            }
        }

        return false;
    }

    /**
     * @return MagicUrlFactory
     * @throws \Exception
     */
    public static function createFactoryWithRules()
    {
        $factory = new self;

        $client = new GuzzleClient();
        $client->setOption('timeout', 30000);

        $cacheInterval = new \DateInterval('PT5M');
        $cachedClient = new FileCacheDecorator($client, null, $cacheInterval);

        $factory->attachRule(new FromRule($cachedClient));
        $factory->attachRule(new DateRule());

        return $factory;
    }
}
