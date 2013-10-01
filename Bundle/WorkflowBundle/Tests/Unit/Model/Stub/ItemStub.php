<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model\Stub;

class ItemStub
{
    protected $data = array();

    public function __construct(array $data = array())
    {
        $this->data = $data;
    }

    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    public function __get($name)
    {
        return isset($this->data[$name]) ? $this->data[$name] : null;
    }

    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    public function getData()
    {
        return $this->data;
    }
}
