<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Reader;

use PhpSpec\ObjectBehavior;

class DummyReaderSpec extends ObjectBehavior
{
    function it_is_an_item_reader()
    {
        $this->shouldImplement('\Akeneo\Bundle\BatchBundle\Item\ItemReaderInterface');
    }

    function it_does_not_read_anything()
    {
        $this->read()->shouldReturn(null);
    }

    function it_provides_configuration_fields()
    {
        $this->getConfigurationFields()->shouldReturn([]);
    }
}
