<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Factory\Write\Value;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;

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
            throw new \InvalidArgumentException('Scalar value cannot be null');
        }

        if (!is_scalar($data)) {
            throw InvalidPropertyTypeException::scalarExpected(
                $attribute->getCode(),
                static::class,
                $data
            );
        }

        if (is_string($data) && '' === trim($data)) {
            throw new \InvalidArgumentException('Scalar value cannot be empty');
        }

        if (AttributeTypes::BOOLEAN === $attribute->getType()) {
            if (is_bool($data)) {
                $data = boolval($data);
            } else {
                throw new InvalidArgumentException('Scalar value for boolean attribute type should a boolean');
            }
        }

        return $data;
    }
}
