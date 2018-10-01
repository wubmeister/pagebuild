<?php

namespace AuthKit\Authenticate\Adapter;

class AbstractAdapter
{
    const STATUS_INITIAL = 0;
    const STATUS_PENDING = 1;
    const STATUS_SUCCESS = 2;
    const STATUS_ERROR = 3;

    protected $status = self::STATUS_INITIAL;
    protected $identity;

    abstract public function handleRequest($request);

    public function getStatus()
    {
        return $this->status;
    }

    public function getIdentity()
    {
        return $this->identity;
    }
}
