<?php

namespace Pim\Component\Connector\ArrayConverter\FlatToStandard;

use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Pim\Component\Connector\ArrayConverter\FieldsRequirementChecker;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\AttributeColumnsResolver;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\ColumnsMapper;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\ColumnsMerger;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\ProductModel\FieldConverter;

/**
 *
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
     * @param ColumnsMapper                   $columnsMapper
     * @param FieldConverter                  $fieldConverter
     * @param ArrayConverterInterface         $productValueConverter
     * @param ColumnsMerger                  $columnsMerger
     * @param AttributeColumnsResolver $attributeColumnsResolver ,
     * @param FieldsRequirementChecker        $fieldsRequirementChecker
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
        $convertedValues = $convertedFlatProductModel = [];
        $flatProductModel = $this->columnsMapper->map($flatProductModel, $options['mapping']);
        $flatProductModel = $this->columnsMerger->merge($flatProductModel);

        $this->fieldsRequirementChecker->checkFieldsPresence($flatProductModel, ['code']);

        foreach ($flatProductModel as $column => $value) {
            if ($this->fieldConverter->supportsColumn($column)) {
                $convertedFields = $this->fieldConverter->convert($column, $value);
                /**
                 * TODO: PIM-6444, when the variant group will be removed, we should remove this loop because
                 * the field converter should return a simple object instead an array.
                 */
                foreach ($convertedFields as $convertedField) {
                    $convertedFlatProductModel = $convertedField->appendTo($convertedFlatProductModel);
                }
            } else {
                $convertedValues[$column] = $value;
            }
        }

        $convertedValues = $this->productValueConverter->convert($convertedValues);
        $convertedFlatProductModel['values'] = $convertedValues;

        return $convertedFlatProductModel;
    }
}
