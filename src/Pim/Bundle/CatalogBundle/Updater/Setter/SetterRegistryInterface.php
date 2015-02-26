<?php

namespace Pim\Bundle\CatalogBundle\Updater\Setter;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

/**
 * Registry of setters
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface SetterRegistryInterface
{
    /**
     * Register a setter
     *
     * @param SetterInterface $setter
     */
    public function register(SetterInterface $setter);

    /**
     * Fetch the setter which supports the attribute
     *
     * @param AttributeInterface $attribute
     *
     * @throws \LogicException
     *
     * @return SetterInterface
     *
     * @deprecated will be removed in 1.5, use getAttributeSetter
     */
    public function get(AttributeInterface $attribute);

    /**
     * Get the setter (field or attribute)
     *
     * @param string $code the field or the attribute code
     *
     * @return SetterInterface|null
     */
    public function getSetter($code);

    /**
     * Get the field setter
     *
     * @param string $field the field
     *
     * @return SetterInterface|null
     */
    public function getFieldSetter($field);

    /**
     * Get the attribute setter
     *
     * @param AttributeInterface $attribute
     *
     * @return SetterInterface|null
     */
    public function getAttributeSetter(AttributeInterface $attribute);
}
