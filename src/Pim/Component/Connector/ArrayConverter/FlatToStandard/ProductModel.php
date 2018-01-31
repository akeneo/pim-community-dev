<?php

declare(strict_types=1);

namespace Pim\Component\Connector\ArrayConverter\FlatToStandard;

use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Pim\Component\Connector\ArrayConverter\FieldsRequirementChecker;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\AttributeColumnsResolver;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\ColumnsMapper;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\ColumnsMerger;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\ProductModel\FieldConverter;
use Pim\Component\Connector\Exception\DataArrayConversionException;
use Pim\Component\Connector\Exception\StructureArrayConversionException;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModel implements ArrayConverterInterface
{
    /** @var ColumnsMapper */
    private $columnsMapper;

    /** @var FieldConverter */
    private $fieldConverter;

    /** @var ArrayConverterInterface */
    private $productValueConverter;

    /** @var ColumnsMerger */
    private $columnsMerger;

    /** @var AttributeColumnsResolver */
    private $attributeColumnsResolver;

    /** @var FieldsRequirementChecker */
    private $fieldsRequirementChecker;

    /**
     * @param ColumnsMapper            $columnsMapper
     * @param FieldConverter           $fieldConverter
     * @param ArrayConverterInterface  $productValueConverter
     * @param ColumnsMerger            $columnsMerger
     * @param AttributeColumnsResolver $attributeColumnsResolver ,
     * @param FieldsRequirementChecker $fieldsRequirementChecker
     */
    public function __construct(
        ColumnsMapper $columnsMapper,
        FieldConverter $fieldConverter,
        ArrayConverterInterface $productValueConverter,
        ColumnsMerger $columnsMerger,
        AttributeColumnsResolver $attributeColumnsResolver,
        FieldsRequirementChecker $fieldsRequirementChecker
    ) {
        $this->columnsMapper = $columnsMapper;
        $this->fieldConverter = $fieldConverter;
        $this->productValueConverter = $productValueConverter;
        $this->columnsMerger = $columnsMerger;
        $this->attributeColumnsResolver = $attributeColumnsResolver;
        $this->fieldsRequirementChecker = $fieldsRequirementChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function convert(array $flatProductModel, array $options = []): array
    {
        $mappedFlatProductModel = $this->mapFields($flatProductModel, $options);
        $this->validateItem($mappedFlatProductModel);
        $mergedFlatProductModel = $this->columnsMerger->merge($mappedFlatProductModel);
        $convertedProductModel = $this->convertItem($mergedFlatProductModel);

        return $convertedProductModel;
    }

    /**
     * @param array $flatProductModel
     * @param array $options
     *
     * @return array
     */
    private function mapFields(array $flatProductModel, array $options): array
    {
        if (isset($options['mapping'])) {
            $flatProductModel = $this->columnsMapper->map($flatProductModel, $options['mapping']);
        }

        return $flatProductModel;
    }

    /**
     * @param array $mappedFlatProductModel
     */
    protected function validateItem(array $mappedFlatProductModel): void
    {
        $this->fieldsRequirementChecker->checkFieldsPresence($mappedFlatProductModel, ['code']);
        $this->validateOptionalFields($mappedFlatProductModel);
        $this->validateFieldValueTypes($mappedFlatProductModel);
    }

    /**
     * @param array $mappedFlatProductModel
     *
     * @throws StructureArrayConversionException
     */
    protected function validateOptionalFields(array $mappedFlatProductModel): void
    {
        $optionalFields = array_merge(
            ['categories', 'code', 'family_variant', 'parent'],
            $this->attributeColumnsResolver->resolveAttributeColumns()
        );

        // index $optionalFields by keys to improve performances
        $optionalFields = array_combine($optionalFields, $optionalFields);
        $unknownFields = [];

        foreach (array_keys($mappedFlatProductModel) as $field) {
            if (!isset($optionalFields[$field])) {
                $unknownFields[] = $field;
            }
        }

        if (0 < count($unknownFields)) {
            $message = count($unknownFields) > 1 ? 'The fields "%s" do not exist' : 'The field "%s" does not exist';

            throw new StructureArrayConversionException(sprintf($message, implode(', ', $unknownFields)));
        }
    }

    /**
     * @param array $mappedFlatProductModel
     *
     * @throws DataArrayConversionException
     */
    protected function validateFieldValueTypes(array $mappedFlatProductModel): void
    {
        $stringFields = ['code', 'categories', 'family_variant', 'parent'];

        foreach ($mappedFlatProductModel as $field => $value) {
            if (in_array($field, $stringFields) && !is_scalar($value)) {
                throw new DataArrayConversionException(
                    sprintf('The field "%s" should contain a scalar, "%s" provided', $field, gettype($value))
                );
            }
        }
    }

    /**
     * @param array $mergedFlatProductModel
     *
     * @return array
     */
    protected function convertItem(array $mergedFlatProductModel): array
    {
        $convertedValues = $convertedFlatProductModel = [];
        foreach ($mergedFlatProductModel as $column => $value) {
            if ($this->fieldConverter->supportsColumn($column)) {
                $convertedField = $this->fieldConverter->convert($column, $value);
                $convertedFlatProductModel = $convertedField->appendTo($convertedFlatProductModel);
            } else {
                $convertedValues[$column] = $value;
            }
        }

        $convertedValues = $this->productValueConverter->convert($convertedValues);
        $convertedFlatProductModel['values'] = $convertedValues;

        return $convertedFlatProductModel;
    }
}
