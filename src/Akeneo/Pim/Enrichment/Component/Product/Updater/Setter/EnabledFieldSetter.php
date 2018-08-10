<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Updater\Setter;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

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
    public function setFieldData($product, $field, $data, array $options = [])
    {
        if (!$product instanceof ProductInterface) {
            throw InvalidObjectException::objectExpected($product, ProductInterface::class);
        }

        if (0 === $data || '0' === $data) {
            $data = false;
        }

        if (1 === $data || '1' === $data) {
            $data = true;
        }

        if (!is_bool($data)) {
            throw InvalidPropertyTypeException::booleanExpected(
                $field,
                static::class,
                $data
            );
        }

        $product->setEnabled($data);
    }
}
