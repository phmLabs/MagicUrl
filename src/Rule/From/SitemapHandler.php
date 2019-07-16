<?php

namespace phmLabs\MagicUrl\Rule\From;

use GuzzleHttp\Exception\ClientException;
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

        try {
            $request = new BrowserRequest('get', $sitemapUrl);
            $response = $this->client->sendRequest($request);
        } catch (ClientException $e) {
            throw new  ResolveException('Unable to get "' . $sitemapUrl . '". Error: ' . $e->getMessage() . '.');
        }

        $body = (string)$response->getBody();

        if ($response->hasHeader('content-type')) {
            $contentType = $response->getHeader('content-type');
            if (is_array($contentType) && in_array(strtolower($contentType[0]), $this->gzipContentTypes)) {
                $body = gzdecode((string)$response->getBody());
            }
        }

        $sitemapContent = $body;

        $reader = new \XMLReader;

        $reader->xml($sitemapContent);

        $count = 0;
        $currentElement = 0;

        while ($reader->read()) {
            if ($reader->name == 'loc' && $reader->nodeType == \XMLReader::ELEMENT) {
                $count++;
                $currentElement++;

                if ($currentElement == $elementNumber) {
                    $link = $reader->readInnerXml();

                    $link = str_replace("<![CDATA[", "", $link);
                    $link = str_replace("]]>", "", $link);

                    return $link;
                }
            }
        }
        throw new  ResolveException('The sitemap does only provide ' . $count . ' elements. Element number ' . $elementNumber . ' was requested.');
    }
}
