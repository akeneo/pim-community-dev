<?php

namespace spec\Pim\Bundle\CatalogBundle\Command;

use Pim\Bundle\CatalogBundle\Command\ResetIndexesCommand;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ResetIndexesCommandSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ResetIndexesCommand::class);
    }
}
