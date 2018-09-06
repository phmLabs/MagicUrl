<?php

namespace phmLabs\MagicUrl\Rule\From;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use Koalamon\Client\Reporter\Event;
use phm\HttpWebdriverClient\Http\Request\BrowserRequest;
use phmLabs\MagicUrl\Rule\ResolveException;
use phm\HttpWebdriverClient\Http\Client\HttpClient;

class SitemapHandler implements Handler
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
    public function resolve($sitemapUrl, $elementNumber = 1)
    {
        $elementNumber = max(1, $elementNumber);

        $request = new BrowserRequest('get', $sitemapUrl);
        $response = $this->client->sendRequest($request);

        $sitemapContent = (string)$response->getBody();

        $doc = new \DOMDocument();
        $doc->loadXML($sitemapContent);

        $xpath = new \DOMXPath($doc);

        $urlQuery = '//urlset/url/loc/text()';

        $entries = $xpath->query($urlQuery);

        $locations = $doc->getElementsByTagName('loc');

        $urls = array();

        foreach ($locations as $location) {
            $urls[] = $location->nodeValue;
        }

        var_dump($urls[$elementNumber - 1]);

        return $urls[$elementNumber - 1];
    }
}
