<?php

namespace spec\Pim\Component\Connector\Writer\File;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class XlsxWriterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Connector\Writer\File\XlsxWriter');
    }

    function it_is_a_configurable_step()
    {
        $this->shouldHaveType('Akeneo\Component\Batch\Item\AbstractConfigurableStepElement');
    }

    function it_is_a_writer()
    {
        $this->shouldImplement('Akeneo\Component\Batch\Item\ItemWriterInterface');
    }

    function it_has_configuration()
    {
        $this->getConfigurationFields();
    }

    function it_write_xlsx_file()
    {
        $this->write([]);
    }
}
