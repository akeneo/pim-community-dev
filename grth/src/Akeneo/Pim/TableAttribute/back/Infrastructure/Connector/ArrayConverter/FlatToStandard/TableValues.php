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

namespace Akeneo\Pim\TableAttribute\Infrastructure\Connector\ArrayConverter\FlatToStandard;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\BooleanColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\MeasurementColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker;
use Akeneo\Tool\Component\Connector\Exception\DataArrayConversionException;

final class TableValues implements ArrayConverterInterface
{
    private const ATTRIBUTE_COLUMN = 'attribute';

    private FieldsRequirementChecker $fieldsRequirementChecker;
    private GetAttributes $getAttributes;
    private TableConfigurationRepository $tableConfigurationRepository;
    private string $entityType;

    public function __construct(
        FieldsRequirementChecker $fieldsRequirementChecker,
        GetAttributes $getAttributes,
        TableConfigurationRepository $tableConfigurationRepository,
        string $entityType
    ) {
        $this->fieldsRequirementChecker = $fieldsRequirementChecker;
        $this->getAttributes = $getAttributes;
        $this->tableConfigurationRepository = $tableConfigurationRepository;
        $this->entityType = $entityType;
    }

    public function convert(array $item, array $options = []): array
    {
        $this->fieldsRequirementChecker->checkFieldsPresence($item, [$this->entityType, self::ATTRIBUTE_COLUMN]);
        $this->fieldsRequirementChecker->checkFieldsFilling($item, [$this->entityType, self::ATTRIBUTE_COLUMN]);

        $attributeParts = \explode('-', (string) $item[self::ATTRIBUTE_COLUMN]);
        $attribute = $this->getAttributes->forCode($attributeParts[0]);

        if (null === $attribute) {
            throw new DataArrayConversionException(\sprintf("The '%s' attribute is unknown", $attributeParts[0]));
        }

        if ($attribute->type() !== AttributeTypes::TABLE) {
            throw new DataArrayConversionException(
                \sprintf("The '%s' attribute is not a table attribute", $attribute->code())
            );
        }

        $locale = $scope = null;
        if ($attribute->isLocalizable() && $attribute->isScopable()) {
            $locale = $attributeParts[1] ?? null;
            $scope = $attributeParts[2] ?? null;
        } elseif ($attribute->isLocalizable()) {
            $locale = $attributeParts[1] ?? null;
        } elseif ($attribute->isScopable()) {
            $scope = $attributeParts[1] ?? null;
        }

        return [
            'entity' => (string) $item[$this->entityType],
            'attribute_code' => $attribute->code(),
            'locale' => $locale,
            'scope' => $scope,
            'row_values' => $this->computeTableValues($item, $attribute),
        ];
    }

    private function computeTableValues(array $item, Attribute $attribute): array
    {
        $rowValues = \array_filter(
            $item,
            fn ($code): bool => !\in_array($code, [$this->entityType, self::ATTRIBUTE_COLUMN]),
            ARRAY_FILTER_USE_KEY
        );

        $unknownColumns = [];
        $tableConfiguration = $this->tableConfigurationRepository->getByAttributeCode($attribute->code());
        foreach ($rowValues as $columnCode => $value) {
            if ('' === \trim((string) $value)) {
                unset($rowValues[$columnCode]);
                continue;
            }

            $column = $tableConfiguration->getColumnByCode(ColumnCode::fromString((string) $columnCode));
            if ($column instanceof BooleanColumn) {
                if (\trim((string) $value) === '1') {
                    $rowValues[$columnCode] = true;
                } elseif (\trim((string) $value) === '0') {
                    $rowValues[$columnCode] = false;
                }
            } elseif ($column instanceof MeasurementColumn) {
                $valueDoesMatch = \preg_match('/^(?P<amount>([^ ]+))[ ]+(?P<unit>[^ ]*)$/', \trim($value), $matches);

                if (1 !== $valueDoesMatch) {
                    throw new DataArrayConversionException(\sprintf('Invalid value format for the measurement column: "%s"', $value));
                }

                $amount = $matches['amount'] ?? null;
                $unit = $matches['unit'] ?? null;

                if (null !== $unit && \is_numeric($amount)) {
                    $rowValues[$columnCode] = [
                        'amount' => (string) $amount,
                        'unit' => (string) $unit,
                    ];
                }
            } elseif (null === $column) {
                $unknownColumns[] = $columnCode;
            }
        }

        if ([] !== $unknownColumns) {
            throw new DataArrayConversionException(\sprintf(
                "The '%s' column%s unknown",
                implode(', ', $unknownColumns),
                count($unknownColumns) > 1 ? 's are' : ' is'
            ));
        }

        return $rowValues;
    }
}
