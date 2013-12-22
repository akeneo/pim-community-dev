<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Pim\Bundle\CatalogBundle\Model\ProductAttributeInterface;

/**
 * Attribute manager interface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductAttributeManagerInterface
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
     * @return string the attribute class
     */
    public function getAttributeClass();

    /**
     * Create a ProductAttributeInterface object from data in the form
     *
     * @param array $data Form data
     *
     * @return ProductAttributeInterface $attribute | null
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
     * Make sure the ProductAttributeInterface entity has the right backend properties
     *
     * @param ProductAttributeInterface $attribute
     *
     * @return ProductAttributeInterface $attribute
     */
    public function prepareBackendProperties(ProductAttributeInterface $attribute);
}
