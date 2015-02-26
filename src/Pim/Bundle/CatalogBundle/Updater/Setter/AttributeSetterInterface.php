<?php

namespace Pim\Bundle\CatalogBundle\Updater\Setter;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;

/**
 * Sets a value in a product
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface AttributeSetterInterface extends SetterInterface
{
    /**
     * Supports the attribute
     *
     * @param AttributeInterface $attribute
     *
     * @return boolean
     */
    public function supportsAttribute(AttributeInterface $attribute);
}
