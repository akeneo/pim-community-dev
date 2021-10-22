<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Infrastructure\Value\Factory;

use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\ValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Domain\Value\Table;
use Akeneo\Pim\TableAttribute\Infrastructure\Value\TableValue;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

class TableValueFactory implements ValueFactory
{
    private TableConfigurationRepository $tableConfigurationRepository;

    public function __construct(TableConfigurationRepository $tableConfigurationRepository)
    {
        $this->tableConfigurationRepository = $tableConfigurationRepository;
    }

    public function createByCheckingData(
        Attribute $attribute,
        ?string $channelCode,
        ?string $localeCode,
        $data
    ): ValueInterface {
        if (!is_array($data)) {
            throw InvalidPropertyTypeException::arrayExpected($attribute->code(), static::class, $data);
        }
        foreach ($data as $row) {
            if (!is_array($row)) {
                throw InvalidPropertyTypeException::arrayOfArraysExpected($attribute->code(), static::class, $data);
            }

            foreach ($row as $cell) {
                if (null === $cell || '' === $cell) {
                    continue;
                }

                if (!is_scalar($cell)) {
                    throw InvalidPropertyTypeException::validArrayStructureExpected(
                        $attribute->code(),
                        'The cell value must be a text string, a number or a boolean.',
                        static::class,
                        $data
                    );
                }
            }
        }

        return $this->createWithoutCheckingData($attribute, $channelCode, $localeCode, $data);
    }

    public function createWithoutCheckingData(
        Attribute $attribute,
        ?string $channelCode,
        ?string $localeCode,
        $data
    ): ValueInterface {
        $data = $this->removeDuplicateOnFirstColumn($attribute, $data);
        $table = Table::fromNormalized($data);
        if ($attribute->isLocalizableAndScopable()) {
            return TableValue::scopableLocalizableValue($attribute->code(), $table, $channelCode, $localeCode);
        }
        if ($attribute->isScopable()) {
            return TableValue::scopableValue($attribute->code(), $table, $channelCode);
        }
        if ($attribute->isLocalizable()) {
            return TableValue::localizableValue($attribute->code(), $table, $localeCode);
        }

        return TableValue::value($attribute->code(), $table);
    }

    private function removeDuplicateOnFirstColumn(Attribute $attribute, array $data): array
    {
        $tableConfiguration = $this->tableConfigurationRepository->getByAttributeCode($attribute->code());
        $firstColumnCode = \strtolower($tableConfiguration->getFirstColumnCode()->asString());

        $foundOptionCodes = [];
        foreach ($data as $rowIndex => $row) {
            foreach ($row as $columnCode => $value) {
                if ($firstColumnCode === \strtolower((string) $columnCode)) {
                    $optionCode = \strtolower((string) $value);
                    if (array_key_exists($optionCode, $foundOptionCodes)) {
                        unset($data[$foundOptionCodes[$optionCode]]);
                    }
                    $foundOptionCodes[$optionCode] = $rowIndex;
                }
            }
        }

        return $data;
    }

    public function supportedAttributeType(): string
    {
        return AttributeTypes::TABLE;
    }
}
