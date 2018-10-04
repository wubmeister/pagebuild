<?php

namespace AuthKit;

use Psr\Http\Message\ServerRequestInterface;
use AuthKit\Authenticate\Adapter\AbstractAdapter;
use AuthKit\Authenticate\Storage\StorageInterface;

class Auth
{
    protected $adapter;
    protected $storage;

    public function __construct(Adapter $adapter, Storage $storage)
    {
        $this->adapter = $adapter;
        $this->storage = $storage;
    }

    public function getIdentity()
    {
        return $this->storage->getIdentity();
    }

    public function hasIdentity()
    {
        return $this->storage->hasIdentity();
    }

    public function authenticate(ServerRequestInterface $request, callable $onSuccess = null, callable $onError = null)
    {
        $this->adapter->handleRequest($request);
        $status = $this->adapter->getStatus();
        if ($status == AbstractAdapter::STATUS_SUCCESS) {
            $identity = $this->adapter->getIdentity();
            $this->storage->setIdentity($identity);
            if ($onSuccess) {
                $onSuccess($identity);
            }
        } else if ($status == AbstractAdapter::STATUS_ERROR) {
            if ($onError) {
                $onError($this->adapter->getError());
            }
        }
    }
}
