<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Factory\Value;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

/**
 * Factory that creates date product values.
 *
 * @internal  Please, do not use this class directly. You must use \Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DateValueFactory extends AbstractValueFactory
{
    /**
     * {@inheritdoc}
     */
    protected function prepareData(AttributeInterface $attribute, $data, bool $ignoreUnknownData)
    {
        if (null === $data) {
            return null;
        }

        if (!\is_string($data)) {
            throw InvalidPropertyTypeException::stringExpected(
                $attribute->getCode(),
                static::class,
                $data
            );
        }

        $matches = [];
        if (!\preg_match('/^\d{4}-\d{2}-\d{2}/', $data, $matches)) {
            throw $this->buildInvalidDateException($attribute, $data);
        }

        list($year, $month, $day) = \explode('-', $matches[0]);
        if (true !== \checkdate($month, $day, $year)) {
            throw InvalidPropertyException::validDateExpected(
                $attribute->getCode(),
                static::class,
                $data
            );
        }

        try {
            $date = new \DateTime($data);
        } catch (\Exception $e) {
            throw $this->buildInvalidDateException($attribute, $data);
        }

        return $date;
    }

    protected function buildInvalidDateException(AttributeInterface $attribute, $data): InvalidPropertyException
    {
        return InvalidPropertyException::dateExpected(
            $attribute->getCode(),
            'yyyy-mm-dd',
            static::class,
            $data
        );
    }
}
