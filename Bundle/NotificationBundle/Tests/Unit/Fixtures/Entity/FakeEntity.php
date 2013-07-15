<?php

class FakeEntity
{
    /**
     * @var
     */
    protected $something;

    /**
     * @param null $params
     */
    public function doSomethingUsefull($params = null)
    {
        $someObj = new \stdClass();
        $this->getSomething()->dispatch('oro.event.good_happens', $someObj);
    }

    public function getSomething()
    {
        return $this->something;
    }
}