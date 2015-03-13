<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Reader\File;

use PhpSpec\ObjectBehavior;

class FileReaderSpec extends ObjectBehavior
{
    function it_gives_configuration_fields()
    {
        $this->getConfigurationFields()->shouldReturn([]);
    }

    function it_is_configurable()
    {
        $this->getFilePath()->shouldReturn(null);

        $this->setFilePath('/MyFolder/');
        $this->getFilePath()->shouldReturn('/MyFolder/');
    }

    function it_cant_read_anything()
    {
        $this->shouldThrow(new \Exception('Not implemented yet.'))->duringRead();
    }
}
