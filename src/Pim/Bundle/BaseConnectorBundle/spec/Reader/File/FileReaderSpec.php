<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Reader\File;

use PhpSpec\ObjectBehavior;

class FileReaderSpec extends ObjectBehavior
{
    function it_cant_read_anything()
    {
        $this->shouldThrow(new \Exception('Not implemented yet.'))->duringRead();
    }
}
