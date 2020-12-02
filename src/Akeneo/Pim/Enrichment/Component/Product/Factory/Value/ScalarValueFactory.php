<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Factory\Value;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

/**
 * Factory that creates simple product values (text, textarea and number).
 *
 * @internal  Please, do not use this class directly. You must use \Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ScalarValueFactory extends AbstractValueFactory
{
    /**
     * {@inheritdoc}
     */
    protected function prepareData(AttributeInterface $attribute, $data, bool $ignoreUnknownData)
    {
        if (null === $data) {
            return;
        }

        if (!\is_scalar($data)) {
            throw InvalidPropertyTypeException::scalarExpected(
                $attribute->getCode(),
                static::class,
                $data
            );
        }

        if (\is_string($data) && '' === \trim($data)) {
            $data = null;
        }

        if (AttributeTypes::BOOLEAN === $attribute->getType() &&
            (1 === $data || '1' === $data || 0 === $data || '0' === $data)
        ) {
            $data = \boolval($data);
        }

        return $data;
    }
}
