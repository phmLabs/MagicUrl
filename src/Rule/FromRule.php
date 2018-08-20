<?php

namespace phmLabs\MagicUrl\Rule;

use GuzzleHttp\Client;
use phmLabs\MagicUrl\Rule\From\UrlHandler;

class FromRule implements Rule
{
    private $client;

    protected $prefix = 'from(';

    private $handlers = [];

    public function __construct(Client $client)
    {
        $this->client = $client;

        $this->handlers['url'] = new UrlHandler($client);
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

            $parameterString = substr($urlString, strlen($this->prefix), strlen($urlString) - strlen($this->prefix) - 1);
            $parameters = explode(',', $parameterString);

            foreach ($parameters as $key => $parameter) {
                $parameters[$key] = trim($parameter);
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
