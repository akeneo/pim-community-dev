<?php

namespace Akeneo\Component\Batch\Item;

use Doctrine\Common\Util\Inflector;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Define a configurable step element
 *
 * @abstract
 */
abstract class AbstractConfigurableStepElement
{
    /**
     * Return name
     *
     * @return string
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
     * Override to add custom logic on step initialization.
     */
    public function initialize()
    {
    }

    /**
     * Override to add custom logic on step completion.
     */
    public function flush()
    {
    }
}
