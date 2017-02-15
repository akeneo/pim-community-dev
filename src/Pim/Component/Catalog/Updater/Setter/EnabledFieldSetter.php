<?php

namespace Pim\Component\Catalog\Updater\Setter;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Pim\Component\Catalog\Model\ProductInterface;

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
