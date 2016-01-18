<?php

namespace Pim\Component\Catalog\Updater\Setter;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 * Set the enabled field of a product
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EnabledFieldSetter extends AbstractFieldSetter
{
    /**
     * @param array $supportedFields
     */
    public function __construct(array $supportedFields)
    {
        $this->supportedFields = $supportedFields;
    }

    /**
     * {@inheritdoc}
     *
     * Expected data input format : true|false
     */
    public function setFieldData(ProductInterface $product, $field, $data, array $options = [])
    {
        $product->setEnabled($data);
    }
}
