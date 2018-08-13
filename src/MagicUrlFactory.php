<?php

namespace phmLabs\MagicUrl;

use GuzzleHttp\Psr7\Uri;
use phmLabs\MagicUrl\Rule\Rule;

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
}