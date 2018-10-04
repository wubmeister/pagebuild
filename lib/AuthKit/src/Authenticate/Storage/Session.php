<?php

namespace AuthKit\Authenticate\Storage;

use AuthKit\Authenticate\Identity;

class Session implements StorageInterface
{
    public function storeIdentity(Identity $identity)
    {
        $_SESSION['authkit_identity'] = $identity;
    }

    public function getIdentity()
    {
        return $_SESSION['authkit_identity'];
    }

    public function hasIdentity()
    {
        return isset($_SESSION['authkit_identity']);
    }
}
