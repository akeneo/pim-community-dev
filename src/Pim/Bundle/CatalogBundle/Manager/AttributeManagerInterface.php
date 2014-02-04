<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttributeOption;
use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttributeOptionValue;

/**
 * Attribute manager interface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface AttributeManagerInterface
{
    /**
     * Return a new attribute instance
     *
     * @param string $type attribute type
     *
     * @return AbstractAttribute
     */
    public function createAttribute($type = null);

    /**
     * Return a new instance
     *
     * @return AbstractAttributeOption
     */
    public function createAttributeOption();

    /**
     * Return a new instance
     *
     * @return AbstractAttributeOptionValue
     */
    public function createAttributeOptionValue();

    /**
     * @return string the attribute class
     */
    public function getAttributeClass();

    /**
     * @return string the attribute option class
     */
    public function getAttributeOptionClass();

    /**
     * Create an AbstractAttribute object from data in the form
     *
     * @param array $data Form data
     *
     * @return AbstractAttribute $attribute | null
     */
    public function createAttributeFromFormData($data);

    /**
     * Prepare data for binding to the form
     *
     * @param array $data Form data
     *
     * @return array Prepared form data
     */
    public function prepareFormData($data);

    /**
     * Return an array of available attribute types
     *
     * @return array $types
     */
    public function getAttributeTypes();

    /**
     * Make sure the AbstractAttribute entity has the right backend properties
     *
     * @param AbstractAttribute $attribute
     *
     * @return AbstractAttribute $attribute
     */
    public function prepareBackendProperties(AbstractAttribute $attribute);
}
