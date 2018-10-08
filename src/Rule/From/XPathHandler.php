<?php

namespace phmLabs\MagicUrl\Rule\From;

use GuzzleHttp\Exception\ClientException;
use phm\HttpWebdriverClient\Http\Request\BrowserRequest;
use phmLabs\MagicUrl\Rule\ResolveException;
use phm\HttpWebdriverClient\Http\Client\HttpClient;
use whm\Html\Uri;


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
        $elementNumber = max(0, $elementNumber - 1);

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

        $results = @$domXPath->query($xPath);

        if ($results === false) {
            throw new ResolveException('The given xpath is not valid.');
        }

        if ($results->length == 0) {
            throw new ResolveException('The given xpath does not return any elements.');
        }

        if ($results->length < $elementNumber + 1) {
            throw new ResolveException('The given xpath does only return ' . $results->length . ' elements. You ask for element number ' . ($elementNumber + 1) . '.');
        }

        $origin = new Uri($url);

        $url = $results[$elementNumber]->value;

        $absoluteUri = Uri::createAbsoluteUrl(new Uri($url), $origin);

        return (string)$absoluteUri;
    }
}
