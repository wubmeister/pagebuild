<?php

namespace TemplateKit;

class Template implements TemplateInterface
{
    protected $parent;
    protected $variables;
    protected $file;

    public function __construct($file)
    {
        $this->file = $file;
    }

    public function assign(string $name, $value)
    {
        if ($value instanceof Template) {
            $value->parent = $this;
        }

        $this->variables[$name] = $value;
    }

    public function unassign(string $name)
    {
        if (isset($this->variables[$name])) {
            unset($this->variables[$name])
        }
    }

    protected function getVariables()
    {
        $variables = $this->variables;
        if ($this->parent) {
            $variables = array_merge($this->parent->getVariables(), $variables);
        }

        return $variables;
    }

    public function render()
    {
        $variables = $this->getVariables();
        extract($variables);

        ob_start();
        include $this->file;
        $contents = ob_get_contents();
        ob_end_clean();

        return $contents;
    }
}
