<?php

namespace Akeneo\Component\Batch\Item;

use Akeneo\Component\Batch\Step\StepElementInterface;
use Doctrine\Common\Util\Inflector;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Define a configurable step element
 *
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
abstract class AbstractConfigurableStepElement implements StepElementInterface, InitializableInterface,
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
