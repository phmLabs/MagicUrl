<?php

namespace phmLabs\MagicUrl\Rule\From;

use GuzzleHttp\Exception\ClientException;
use phm\HttpWebdriverClient\Http\Request\BrowserRequest;
use phmLabs\MagicUrl\Rule\ResolveException;
use phm\HttpWebdriverClient\Http\Client\HttpClient;

class RssFeedHandler implements Handler
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
    public function resolve($rssFeedUrl, $elementNumber = 1)
    {
        $elementNumber = max(1, $elementNumber);

        try {
            $request = new BrowserRequest('get', $rssFeedUrl);
            $response = $this->client->sendRequest($request);
        } catch (ClientException $e) {
            throw new  ResolveException('Unable to get "' . $rssFeedUrl . '". Error: ' . $e->getMessage() . '.');
        }

        $rssContent = (string)$response->getBody();


        $reader = new \XMLReader;

        $reader->xml($rssContent);

        $count = 0;
        $currentElement = 0;

        while ($reader->read()) {
            if ($reader->name == 'link' && $reader->nodeType == \XMLReader::ELEMENT) {
                $count++;
                $currentElement++;

                // @readme the plus one is needed because the feed has a link on its own
                if ($currentElement == $elementNumber +1 ) {
                    return $reader->readInnerXml();
                }
            }
        }
        throw new  ResolveException('The sitemap does only provide ' . $count . ' elements. Element number ' . $elementNumber . ' was requested.');
    }
}
