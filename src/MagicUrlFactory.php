<?php

namespace phmLabs\MagicUrl;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Uri;
use phmLabs\MagicUrl\Rule\FromRule;
use phmLabs\MagicUrl\Rule\ResolveException;
use phmLabs\MagicUrl\Rule\Rule;

class MagicUrlFactory
{
    const PREFIX = '@';

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
        if (strpos($urlString, self::PREFIX) !== 0) {
            return new Uri($urlString);
        }

        $result = substr($urlString, strlen(self::PREFIX));

        $resolvedUrl = $this->resolveRules($result);

        if (!filter_var($resolvedUrl, FILTER_VALIDATE_URL)) {
            throw new ResolveException('The final resolved url string is not a valid url.');
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

            if($part === $initialPart) {
                throw new ResolveException('Unable to resolve ' . $part . ', no matching rule found.');
            }
        } catch (ResolveException $e) {
            throw new ResolveException('Unable to resolve ' . $part . ' with message: ' . $e->getMessage());
        }

        return $part;
    }

    private function resolveRules($urlString)
    {
        $processedString = $urlString;

        while ($rule = $this->getInnerRule($processedString)) {
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
     */
    public static function createFactoryWithRules()
    {
        $factory = new self;

        $factory->attachRule(new FromRule(new Client()));

        return $factory;
    }
}
