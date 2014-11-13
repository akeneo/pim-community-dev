<?php

namespace Pim\Bundle\CatalogBundle\Updater\Setter;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

/**
 * Sets a value in many products
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface SetterInterface
{
    /**
     * Set the value in products
     *
     * @param ProductInterface[] $products
     * @param AttributeInterface $attribute
     * @param mixed              $data
     * @param string             $locale
     * @param string             $scope
     */
    public function setValue(array $products, AttributeInterface $attribute, $data, $locale = null, $scope = null);

    /**
     * Supports the attribute
     *
     * @param AttributeInterface $attribute
     *
     * @return boolean
     */
    public function supports(AttributeInterface $attribute);

    /**
     * Return the supported types of the attribute
     *
     * @return array
     */
    public function getSupportedTypes();
}
