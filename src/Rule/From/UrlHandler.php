<?php

namespace phmLabs\MagicUrl\Rule\From;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use phmLabs\MagicUrl\Rule\ResolveException;
use phm\HttpWebdriverClient\Http\Client\HttpClient;

class UrlHandler implements Handler
{
    /**
     * @var HttpClient
     */
    private $client;

    public function __construct(HttpClient $client)
    {
        $this->client = $client;
    }

    /**
     * @param $urlString
     * @return string
     * @throws ResolveException
     */
    public function resolve($urlString, $lineNumber = 1)
    {
        $lineNumber = max(1, $lineNumber);

        try {
            $result = $this->client->sendRequest(new Request('GET', $urlString));
        } catch (ClientException $e) {
            throw new ResolveException("Unable to resolve url " . $urlString . ". Endpoint returned " . $e->getCode() . ' as HTTP status code.');
        } catch (\Exception $e) {
            throw new ResolveException("Unable to resolve url " . $urlString . " with error " . $e->getMessage());
        }

        $contentTypeArray = $result->getHeader("content-type");
        $contentTypeParts = explode(';', $contentTypeArray[0]);

        $plainContent = (string)$result->getBody();

        $plainContentLines = (explode("\n", $plainContent));

        if (count($plainContentLines) < $lineNumber) {
            throw new ResolveException("Trying to select element #" . $lineNumber . ", only " . count($plainContentLines) . ' elements found.');
        }

        $urlLine = $plainContentLines[$lineNumber - 1];

        $url = trim(preg_replace('/\s\s+/', ' ', $urlLine));
        $pos = strpos($url, '://');

        if ($pos === false || $pos > 5) {
            throw new ResolveException("Unable to resolve url " . $urlString . ", result is not valid url scheme. Response starts with: " . htmlspecialchars(substr($url, 0, 20)) . '.');
        }

        return $url;
    }
}
