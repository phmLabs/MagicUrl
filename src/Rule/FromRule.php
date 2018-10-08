<?php

namespace phmLabs\MagicUrl\Rule;

use phmLabs\MagicUrl\Rule\From\SitemapHandler;
use phmLabs\MagicUrl\Rule\From\UrlHandler;
use phm\HttpWebdriverClient\Http\Client\HttpClient;
use phmLabs\MagicUrl\Rule\From\XPathHandler;

class FromRule implements Rule
{
    private $client;

    protected $prefix = 'from(';

    private $handlers = [];

    public function __construct(HttpClient $client)
    {
        $this->client = $client;

        $this->handlers['url'] = new UrlHandler($client);
        $this->handlers['sitemap'] = new SitemapHandler($client);
        $this->handlers['xpath'] = new XPathHandler($client);
    }

    /**
     * @param $urlString
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException | ResolveException
     */
    public function resolve($urlString)
    {
        if (strpos(strtolower($urlString), $this->prefix) === 0) {

            $urlString = trim($urlString);
            preg_match('^".*"^', $urlString, $blocks);

            foreach ($blocks as $key => $block) {
                $urlString = str_replace($block, '#block_' . $key . '#', $urlString);
            }

            $parameterString = substr($urlString, strlen($this->prefix), strlen($urlString) - strlen($this->prefix) - 1);
            $parameters = explode(',', $parameterString);

            foreach ($parameters as $paramNo => $parameter) {

                foreach ($blocks as $key => $block) {
                    $parameter = str_replace('#block_' . $key . '#', $block, $parameter);
                }

                $parameters[$paramNo] = trim($parameter, " \t\n\r\0\x0B\"");
            }

            $handlerName = array_shift($parameters);

            $handler = $this->getHandler($handlerName);

            $uri = call_user_func_array(array($handler, 'resolve'), $parameters);

            return $uri;
        } else {
            return $urlString;
        }
    }

    /**
     * @param $key
     * @return Rule
     * @throws ResolveException
     */
    private function getHandler($key)
    {
        if (array_key_exists($key, $this->handlers)) {
            return $this->handlers[$key];
        } else {
            throw new ResolveException('unknown handler "' . $key . '", valid handlers are ' . implode(', ', array_keys($this->handlers)) . '.');
        }
    }
}
