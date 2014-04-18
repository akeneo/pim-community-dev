<?php

namespace spec\Pim\Bundle\EnrichBundle\MassEditAction;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class MassEditActionOperatorFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\EnrichBundle\MassEditAction\MassEditActionOperatorFactory');
    }
}
