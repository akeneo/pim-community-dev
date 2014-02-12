<?php

namespace Akeneo\Bundle\BatchBundle\Tests\Unit\Item;

use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Item\ItemReaderInterface;

/**
 * Test helpers class needed as there is no way to create a mock from
 * phpUnit that extends and implements as the same time
 */
abstract class ItemReaderTestHelper extends AbstractConfigurableStepElement implements ItemReaderInterface
{
}
