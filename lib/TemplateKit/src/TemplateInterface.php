<?php

namespace TemplateKit;

interface TemplateInterface
{
    public function assign(string $name, $value);
    public function unassign(string $name);
    public function render();
}
