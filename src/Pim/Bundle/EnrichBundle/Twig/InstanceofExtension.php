<?php

namespace Pim\Bundle\EnrichBundle\Twig;

/**
 * This Twig extension adds the "istanceof" test.
 * Usage: {% if object is instanceof('Acme\\Foo\\Entity') %}
 *
 * @author    Remy Betus <remy.betus@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InstanceofExtension extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'instanceof';
    }

    /**
     * {@inheritdoc}
     */
    public function getTests()
    {
        return [
            new \Twig_SimpleTest('instanceof', [$this, 'isInstanceOf']),
        ];
    }

    /**
     * Checks if given object is of the given class type
     *
     * @param mixed  $object
     * @param string $class
     *
     * @return bool
     */
    public function isInstanceOf($object, $class)
    {
        $reflectionClass = new \ReflectionClass($class);

        return $reflectionClass->isInstance($object);
    }
}
