<?php

namespace Specification\Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\Value;

use Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\Value\TableValueUserIntentFactory;
use PhpSpec\ObjectBehavior;

class TableValueUserIntentFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(TableValueUserIntentFactory::class);
    }
}
