<?php

namespace Oro\Bundle\SearchBundle\Tests\Unit\Fixture;

class Attribute
{
    private $code;

    public function __construct($code)
    {
        $this->code = $code;
    }

    public function __toString()
    {
        return (string) $this->code;
    }

    public function getData()
    {
        return $this->code;
    }
}
