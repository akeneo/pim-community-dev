<?php

namespace Pim\Bundle\CatalogBundle\Updater;

use Pim\Bundle\CatalogBundle\Model\AttributeOptionInterface;

/**
 * Updates an attribute option
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
     * @param mixed                    $data            The data to update
     * @param array                    $options         Options to pass to the setter
     *
     * @return ProductUpdaterInterface
     */
    public function update(AttributeOptionInterface $attributeOption, $data, array $options = []);
}
