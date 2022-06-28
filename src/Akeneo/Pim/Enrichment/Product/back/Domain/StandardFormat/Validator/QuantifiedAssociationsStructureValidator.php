<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Domain\StandardFormat\Validator;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class QuantifiedAssociationsStructureValidator
{
    public function validate(string $field, $data): void
    {
        if (!is_array($data)) {
            throw InvalidPropertyTypeException::arrayExpected(
                $field,
                static::class,
                $data
            );
        }

        foreach ($data as $associationTypeCode => $associationTypeValues) {
            if (!is_array($associationTypeValues)) {
                throw InvalidPropertyTypeException::validArrayStructureExpected(
                    $field,
                    sprintf('"%s" should contain an array', $associationTypeCode),
                    static::class,
                    $data
                );
            }

            foreach ($associationTypeValues as $quantifiedLinkType => $quantifiedLinks) {
                if (!is_string($quantifiedLinkType)) {
                    throw InvalidPropertyTypeException::validArrayStructureExpected(
                        $field,
                        sprintf('entity type in "%s" should be a string', $associationTypeCode),
                        static::class,
                        $data
                    );
                }

                if (!is_array($quantifiedLinks) || !$this->isArraySequential($quantifiedLinks)) {
                    throw InvalidPropertyTypeException::validArrayStructureExpected(
                        $field,
                        sprintf('"%s[%s]" should contain an array', $associationTypeCode, $quantifiedLinkType),
                        static::class,
                        $data
                    );
                }

                foreach ($quantifiedLinks as $quantifiedLink) {
                    foreach (['identifier', 'quantity'] as $requiredKey) {
                        if (!isset($quantifiedLink[$requiredKey])) {
                            throw InvalidPropertyTypeException::validArrayStructureExpected(
                                $field,
                                sprintf('a quantified association should contain the key "%s"', $requiredKey),
                                static::class,
                                $data
                            );
                        }
                    }

                    if (!is_string($quantifiedLink['identifier'])) {
                        throw InvalidPropertyTypeException::validArrayStructureExpected(
                            $field,
                            'a quantified association should contain a valid identifier',
                            static::class,
                            $data
                        );
                    }

                    if (!is_int($quantifiedLink['quantity'])) {
                        throw InvalidPropertyTypeException::validArrayStructureExpected(
                            $field,
                            'a quantified association should contain a valid quantity',
                            static::class,
                            $data
                        );
                    }
                }
            }
        }
    }

    private function isArraySequential(array $data): bool
    {
        return empty($data) || array_keys($data) === range(0, count($data) - 1);
    }
}
