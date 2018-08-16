<?php

namespace phmLabs\MagicUrl\Rule\From;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use phmLabs\MagicUrl\Rule\ResolveException;

class UrlHandler implements Handler
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param $urlString
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException | ResolveException
     */
    public function resolve($urlString, $lineNumber = 1)
    {
        try {
            $result = $this->client->send(new Request('GET', $urlString));
        } catch (ClientException $e) {
            throw new ResolveException("Unable to resolve url " . $urlString . " in UrlPointerRule. Endpoint returned " .$e->getCode() . ' as HTTP status code.');
        } catch (\Exception $e) {
            var_dump(get_class($e));
            throw new ResolveException("Unable to resolve url " . $urlString . " in UrlPointerRule with error " . $e->getMessage());
        }

        $plainContent = (string)$result->getBody();

        $url = trim(preg_replace('/\s\s+/', ' ', $plainContent));
        return $url;
    }
}
