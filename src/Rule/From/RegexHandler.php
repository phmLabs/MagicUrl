<?php

namespace phmLabs\MagicUrl\Rule\From;

use GuzzleHttp\Exception\ClientException;
use phm\HttpWebdriverClient\Http\Request\BrowserRequest;
use phmLabs\MagicUrl\Rule\ResolveException;
use phm\HttpWebdriverClient\Http\Client\HttpClient;
use whm\Html\Uri;


class RegexHandler implements Handler
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
    public function resolve($url, $pattern, $elementNumber = 1)
    {
        $elementNumber = max(0, $elementNumber - 1);

        try {
            $request = new BrowserRequest('get', $url);
            $response = $this->client->sendRequest($request);
        } catch (ClientException $e) {
            throw new ResolveException('Unable to get "' . $url . '". Error: ' . $e->getMessage() . '.');
        }

        $html = (string)$response->getBody();

        ob_start();
        $result = preg_match_all($pattern, $html, $matches);
        $output = ob_get_contents();
        ob_end_flush();

        $error = str_replace('Warning: preg_match_all(): ', '', trim(substr(trim($output), 0, strpos($output, ' in /'))));

        if (strlen($error) > 0) {
            throw new ResolveException('The given regex is not valid. ' . $error);
        }

        if (count($matches[1]) === 0) {
            throw new ResolveException('The given regex (' . $pattern . ') returns no matches.');
        }

        if (count($matches[1]) < $elementNumber) {
            throw new ResolveException('The given regex does only return ' . count($matches[1]) . ' elements. You ask for element number ' . ($elementNumber + 1) . '.');
        }

        $url = $matches[1][$elementNumber];

        if (strpos($url, '//') === 0) {
            $url = 'https:' . $url;
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new ResolveException('The given regex does not return a valid URL. Result was ' . $url . '.');
        }

        return $url;
    }
}
