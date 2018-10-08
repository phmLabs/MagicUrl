<?php

namespace phmLabs\MagicUrl\Rule\From;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use Koalamon\Client\Reporter\Event;
use phm\HttpWebdriverClient\Http\Request\BrowserRequest;
use phmLabs\MagicUrl\Rule\ResolveException;
use phm\HttpWebdriverClient\Http\Client\HttpClient;

class XPathHandler implements Handler
{
    private $client;

    public function __construct(HttpClient $client)
    {
        $this->client = $client;
    }

    /**
     * @param $urlString
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException | ResolveException
     */
    public function resolve($url, $xPath, $elementNumber = 1)
    {
        $elementNumber = max(1, $elementNumber);

        try {
            $request = new BrowserRequest('get', $url);
            $response = $this->client->sendRequest($request);
        } catch (ClientException $e) {
            throw new  ResolveException('Unable to get "' . $url . '". Error: ' . $e->getMessage() . '.');
        }

        $html = (string)$response->getBody();

        $domDocument = new \DOMDocument();
        @$domDocument->loadHTML($html);

        $domXPath = new \DOMXPath($domDocument);

        $results = $domXPath->query($xPath);

        var_dump($results->length);


        throw new  ResolveException('The sitemap does only provide ' . $count . ' elements. Element number ' . $elementNumber . ' was requested.');
    }
}
