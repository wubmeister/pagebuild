<?php

class Acl
{
    protected $roles = [];
    protected $superRole = 'Super';

    public function setSuperRole(string $role)
    {
        $this->superRole = $role;
    }

    public function allow(string $role, string $resource, string $action = '*') {
        $this->setRule($role, $resource, $action, true);
    }

    public function deny(string $role, string $resource, string $action = '*') {
        $this->setRule($role, $resource, $action, false);
    }

    protected function setRule(string $role, string $resource, string $action, bool $allowed)
    {
        if (!isset($this->roles[$role])) {
            $this->createRole($role);
        }

        if (!isset($this->roles[$role]['access'][$resource])) {
            $this->roles[$role]['access'][$resource] = [];
        }

        $this->roles[$role]['access'][$resource][$action] = true;
    }

    public function createRole(string $role, $extends = null)
    {
        $this->roles[$role] = [
            'extends' => $extends,
            'access' => []
        ];
    }

    public function isAllowed(string $role, string $resource, string $action = '*')
    {
        if ($role == $this->superRole) {
            return true;
        }

        if (!isset($this->roles[$role])) {
            return false;
        }

        if (!isset($this->roles[$role]['access'][$resource]) || (!isset($this->roles[$role]['access'][$resource][$action]) && !isset($this->roles[$role]['access'][$resource]['*']))) {
            if ($this->roles[$role]['extends']) {
                return $this->isAllowed($this->roles[$role]['extends'], $resource, $action);
            } else {
                return false;
            }
        }

        if (!isset($this->roles[$role]['access'][$resource][$action])) {
            return $this->roles[$role]['access'][$resource]['*'];
        }

        return $this->roles[$role]['access'][$resource][$action];
    }
}
