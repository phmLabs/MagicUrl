<?php

namespace phmLabs\MagicUrl\Rule;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class UrlPointerRule implements Rule
{
    private $client;

    protected $prefix = 'pointTo:';

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param $urlString
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException | ResolveException
     */
    public function resolve($urlString)
    {
        if (strpos($urlString, $this->prefix) === 0) {
            $endPoint = substr($urlString, strlen($this->prefix));

            try {
                $result = $this->client->send(new Request('GET', $endPoint));
            } catch (\Exception $e) {
                throw new ResolveException("Unable to resolve url " . $urlString . " in UrlPointerRule with error " . $e->getMessage());
            }

            $plainContent = (string)$result->getBody();

            $url = trim(preg_replace('/\s\s+/', ' ', $plainContent));
            return $url;
        } else {
            return $urlString;
        }
    }
}
