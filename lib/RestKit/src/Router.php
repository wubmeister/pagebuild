<?php

namespace RestKit;

use Psr\Http\Message\ServerRequestInterface;

class Router
{
    protected $resources;

    public function __construct($config)
    {
        $this->resources = $config;
    }

    public function parseRequest(ServerRequestInterface $request)
    {
        $urlPath = $request->getUri()->getPath();
        $chunks = explode('/', trim($urlPath, '/'));

        $resources = $this->resources;
        $key = null;
        $id = null;
        $ids = [];
        $tail = '';

        foreach ($chunks as $index => $chunk) {
            if (!$key) {
                $key = $chunk;
                if (isset($resources[$key])) {
                    $resource = $key;
                    $resources = $resource[$key]['children'] ?? [];
                } else {
                    $tail = implode('/', array_slice($chunks, $index));
                    break;
                }
            } else {
                $ids["{$resource}_id"] = $chunk;
                $id = $chunk;
            }
        }

        if ($resource) {
            $request = $request
                ->withAttribute('resource', $resource)
                ->withAttribute('id', $id)
                ->withAttribute('tail', $tail);
            foreach ($ids as $key => $value) {
                $request = $request->withAttribute($key, $value);
            }
        }

        return $request;
    }
}
