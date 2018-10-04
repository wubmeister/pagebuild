<?php

namespace AuthKit\Authenticate\Storage;

use AuthKit\Authenticate\Identity;

interface StorageInterface
{
    public function storeIdentity(Identity $identity);
    public function getIdentity();
    public function hasIdentity();
}
