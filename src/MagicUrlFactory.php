<?php

namespace phmLabs\MagicUrl;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Uri;
use phmLabs\MagicUrl\Rule\Rule;
use phmLabs\MagicUrl\Rule\UrlPointerRule;

class MagicUrlFactory
{
    /**
     * @var Rule[]
     */
    private $rules = [];

    public function attachRule(Rule $rule)
    {
        $this->rules[] = $rule;
    }

    /**
     * @param $urlString
     * @return Uri
     */
    public function resolve($urlString)
    {
        $result = $urlString;

        foreach ($this->rules as $rule) {
            $result = $rule->resolve($result);
        }

        return new Uri($result);
    }

    public static function createFactoryWithRules()
    {
        $factory = new self;

        $factory->attachRule(new UrlPointerRule(new Client()));

        return $factory;
    }
}
