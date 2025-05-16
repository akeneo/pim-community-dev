<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Domain\StandardFormat\Validator;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class QuantifiedAssociationsStructureValidator
{
    private const PRODUCT_LINK_TYPE = 'products';
    private const PRODUCT_UUID_LINK_TYPE = 'product_uuids';
    private const PRODUCT_MODEL_LINK_TYPE = 'product_models';
    private const QUANTIFIED_LINK_TYPES = [self::PRODUCT_LINK_TYPE, self::PRODUCT_UUID_LINK_TYPE, self::PRODUCT_MODEL_LINK_TYPE];

    /**
     * @param mixed $data
     */
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

            if (
                array_key_exists(self::PRODUCT_LINK_TYPE, $associationTypeValues)
                && array_key_exists(self::PRODUCT_UUID_LINK_TYPE, $associationTypeValues)
            ) {
                throw InvalidPropertyTypeException::validArrayStructureExpected(
                    $field,
                    'a quantified association cannot have both "product" and "product_uuid" keys',
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

                if (!in_array($quantifiedLinkType, self::QUANTIFIED_LINK_TYPES)) {
                    throw InvalidPropertyTypeException::validArrayStructureExpected(
                        $field,
                        sprintf(
                            'entity type in "%s" should contain one of these value',
                            implode(',', self::QUANTIFIED_LINK_TYPES)
                        ),
                        static::class,
                        $data
                    );
                }

                foreach ($quantifiedLinks as $quantifiedLink) {
                    if (!isset($quantifiedLink['quantity'])) {
                        throw InvalidPropertyTypeException::validArrayStructureExpected(
                            $field,
                            'a quantified association should contain the key "quantity"',
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

                    if ($quantifiedLinkType === self::PRODUCT_LINK_TYPE) {
                        if (!isset($quantifiedLink['identifier'])) {
                            throw InvalidPropertyTypeException::validArrayStructureExpected(
                                $field,
                                'a quantified association should contain the key "identifier"',
                                static::class,
                                $data
                            );
                        }

                        if (!is_string($quantifiedLink['identifier'])) {
                            throw InvalidPropertyTypeException::validArrayStructureExpected(
                                $field,
                                'a quantified association should contain a valid identifier',
                                static::class,
                                $data
                            );
                        }
                    } elseif ($quantifiedLinkType === self::PRODUCT_UUID_LINK_TYPE) {
                        if (!isset($quantifiedLink['uuid'])) {
                            throw InvalidPropertyTypeException::validArrayStructureExpected(
                                $field,
                                'a quantified association should contain the key "uuid"',
                                static::class,
                                $data
                            );
                        }

                        if (!is_string($quantifiedLink['uuid']) || !Uuid::isValid($quantifiedLink['uuid'])) {
                            throw InvalidPropertyTypeException::validArrayStructureExpected(
                                $field,
                                'a quantified association should contain a valid uuid',
                                static::class,
                                $data
                            );
                        }
                    } elseif ($quantifiedLinkType === self::PRODUCT_MODEL_LINK_TYPE) {
                        if (!isset($quantifiedLink['identifier'])) {
                            throw InvalidPropertyTypeException::validArrayStructureExpected(
                                $field,
                                'a quantified association should contain the key "identifier"',
                                static::class,
                                $data
                            );
                        }

                        if (!is_string($quantifiedLink['identifier'])) {
                            throw InvalidPropertyTypeException::validArrayStructureExpected(
                                $field,
                                'a quantified association should contain a valid identifier',
                                static::class,
                                $data
                            );
                        }
                    }
                }
            }
        }
    }

    /**
     * @param array<int|string, mixed> $data
     */
    private function isArraySequential(array $data): bool
    {
        return empty($data) || array_keys($data) === range(0, count($data) - 1);
    }
}
