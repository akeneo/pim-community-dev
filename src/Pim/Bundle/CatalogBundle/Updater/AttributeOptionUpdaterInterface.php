<?php

namespace Pim\Bundle\CatalogBundle\Updater;

use Pim\Bundle\CatalogBundle\Model\AttributeOptionInterface;

/**
 * Provides basic operations to update an attribute option
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface AttributeOptionUpdaterInterface
{
    /**
     * Sets a data in a product field (erase the current data)
     *
     * @param AttributeOptionInterface $attributeOption The item to update
     * @param string                   $field           The field to update
     * @param mixed                    $data            The data to set
     * @param array                    $options         Options to pass to the setter
     *
     * @return ProductUpdaterInterface
     */
    public function setData(AttributeOptionInterface $attributeOption, $field, $data, array $options = []);
}
