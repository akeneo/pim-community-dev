<?php

namespace Akeneo\Component\Batch\Item;

use Doctrine\Common\Util\Inflector;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Define a configurable step element
 *
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 * @abstract
 */
abstract class AbstractConfigurableStepElement implements StepElementInterface, InitializableInterface,
    FlushableInterface
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        $classname = get_class($this);

        if (preg_match('@\\\\([\w]+)$@', $classname, $matches)) {
            $classname = $matches[1];
        }

        return Inflector::tableize($classname);
    }

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
