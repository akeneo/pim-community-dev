<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Factory\Write\Value;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

/**
 * Factory that creates option (simple-select) product values.
 *
 * @internal  Please, do not use this class directly. You must use \Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class OptionValueFactory extends AbstractValueFactory
{
    public function __construct(
        $productValueClass,
        $supportedAttributeType
    ) {
        parent::__construct($productValueClass, $supportedAttributeType);
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareData(AttributeInterface $attribute, $data, bool $ignoreUnknownData)
    {
        if (null === $data) {
            throw new \InvalidArgumentException('Option value cannot be null');
        }

        // TODO TIP-1325 Fix the cast to string
        if (!(is_string($data) || is_numeric($data)) || trim((string) $data) === '') {
            throw InvalidPropertyTypeException::stringExpected(
                $attribute->getCode(),
                static::class,
                $data
            );
        }

        return (string) $data;
    }
}
