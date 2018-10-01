<?php

namespace AuthKit\Authenticate;

class Identity
{
    public function __construct($data)
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }
}
