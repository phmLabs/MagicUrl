<?php

namespace phmLabs\MagicUrl\Rule;

use GuzzleHttp\Client;

class UrlPointerRule implements Rule
{
    private $client;

    protected $prefix = 'pointerTo:';

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function resolve($urlString)
    {
        if (strpos($urlString, $this->prefix) === 0) {
            $endPoint = substr($urlString, strlen($this->prefix));
            return $endPoint;
        } else {
            return $urlString;
        }
    }
}