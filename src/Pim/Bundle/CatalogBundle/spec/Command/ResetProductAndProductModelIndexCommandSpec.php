<?php

namespace spec\Pim\Bundle\CatalogBundle\Command;

use Pim\Bundle\CatalogBundle\Command\ResetProductAndProductModelIndexCommand;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ResetProductAndProductModelIndexCommandSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ResetProductAndProductModelIndexCommand::class);
    }
}
