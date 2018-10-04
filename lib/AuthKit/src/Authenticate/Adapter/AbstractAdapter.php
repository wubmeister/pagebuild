<?php

namespace AuthKit\Authenticate\Adapter;

use Psr\Http\Message\ServerRequestInterface;

class AbstractAdapter
{
    const STATUS_INITIAL = 0;
    const STATUS_PENDING = 1;
    const STATUS_SUCCESS = 2;
    const STATUS_ERROR = 3;

    protected $status = self::STATUS_INITIAL;
    protected $identity;
    protected $error;

    abstract public function handleRequest(ServerRequestInterface $request);

    public function getStatus()
    {
        return $this->status;
    }

    public function getIdentity()
    {
        return $this->identity;
    }

    public function getError()
    {
        return $this->error;
    }
}
