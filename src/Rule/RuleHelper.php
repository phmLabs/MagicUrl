<?php

namespace phmLabs\MagicUrl\Rule;

abstract class RuleHelper
{
    public static function extractParameters($prefix, $urlString)
    {
        $urlString = trim($urlString);
        preg_match('^".*"^', $urlString, $blocks);

        foreach ($blocks as $key => $block) {
            $urlString = str_replace($block, '#block_' . $key . '#', $urlString);
        }

        $parameterString = substr($urlString, strlen($prefix), strlen($urlString) - strlen($prefix) - 1);

        if (strpos($parameterString, '(') === 0) {
            $parameterString = substr($parameterString, 1);
        }

        $parameters = explode(',', $parameterString);

        foreach ($parameters as $paramNo => $parameter) {

            $parameter = trim($parameter);

            foreach ($blocks as $key => $block) {
                $parameter = str_replace('#block_' . $key . '#', $block, $parameter);
            }

            if (strpos($parameter, "'") === 0 || strpos($parameter, '"') === 0) {
                $parameter = substr($parameter, 1, strlen($parameter) - 2);
            }

            $parameters[$paramNo] = trim($parameter, " \t\n\r\0\x0B\"");
        }

        return $parameters;
    }
}
