<?php

namespace Akeneo\Component\Batch\Item;

use Akeneo\Component\Batch\Step\StepElementInterface;

/**
 * Define a configurable step element
 *
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 * @internal TODO: in fact this class is imho useless since we moved methods in Interface (would drop it)
 */
abstract class AbstractConfigurableStepElement implements
    InitializableInterface,
    FlushableInterface
{
    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
    }
}
