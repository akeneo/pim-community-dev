<?php

namespace Oro\Bundle\NotificationBundle\Tests\Unit\Fixtures\Entity;

class FakeEntity
{
    /**
     * @var mixed
     */
    protected $something;

    public function doSomethingUsefull()
    {
        $someObj = new \stdClass();
        $this->getSomething()->dispatch('oro.event.good_happens_unittest', $someObj);
    }

    public function getSomething()
    {
        return $this->something;
    }
}
