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

        var_dump($result);

        try {
            foreach ($this->rules as $rule) {
                $result = $rule->resolve($result);
            }
        } catch (ResolveException $e) {
            throw new ResolveException('Unable to resolve ' . $urlString . ' with message "' . $e->getMessage());
        }

        return new Uri($result);
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
