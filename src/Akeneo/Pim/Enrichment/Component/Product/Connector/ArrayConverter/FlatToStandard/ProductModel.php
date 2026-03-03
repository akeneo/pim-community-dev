<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ProductModel\FieldConverter;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker;
use Akeneo\Tool\Component\Connector\Exception\DataArrayConversionException;
use Akeneo\Tool\Component\Connector\Exception\StructureArrayConversionException;

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

    /** @var AssociationColumnsResolver */
    private $assocColumnsResolver;

    /** @var array */
    private $optionalAssocFields = [];

    /**
     * @param ColumnsMapper              $columnsMapper
     * @param FieldConverter             $fieldConverter
     * @param ArrayConverterInterface    $productValueConverter
     * @param ColumnsMerger              $columnsMerger
     * @param AttributeColumnsResolver   $attributeColumnsResolver ,
     * @param FieldsRequirementChecker   $fieldsRequirementChecker
     * @param AssociationColumnsResolver $assocColumnsResolver
     */
    public function __construct(
        ColumnsMapper $columnsMapper,
        FieldConverter $fieldConverter,
        ArrayConverterInterface $productValueConverter,
        ColumnsMerger $columnsMerger,
        AttributeColumnsResolver $attributeColumnsResolver,
        FieldsRequirementChecker $fieldsRequirementChecker,
        AssociationColumnsResolver $assocColumnsResolver
    ) {
        $this->columnsMapper = $columnsMapper;
        $this->fieldConverter = $fieldConverter;
        $this->productValueConverter = $productValueConverter;
        $this->columnsMerger = $columnsMerger;
        $this->attributeColumnsResolver = $attributeColumnsResolver;
        $this->fieldsRequirementChecker = $fieldsRequirementChecker;
        $this->assocColumnsResolver = $assocColumnsResolver;
        $this->optionalAssocFields = [];
    }

    /**
     * {@inheritdoc}
     */
    public function convert(array $flatProductModel, array $options = []): array
    {
        $mappedFlatProductModel = $this->mapFields($flatProductModel, $options);
        $filteredItem = $this->filterFields($mappedFlatProductModel, isset($options['with_associations']) ? $options['with_associations'] : true);
        $this->validateItem($filteredItem);

        $mergedFlatProductModel = $this->columnsMerger->merge($filteredItem, $options);
        $convertedProductModel = $this->convertItem($mergedFlatProductModel);

        return $convertedProductModel;
    }

    protected function filterFields(array $mappedItem, bool $withAssociations): array
    {
        if (false === $withAssociations) {
            $isGroupAssociationPattern = sprintf('/^\w+%s$/', AssociationColumnsResolver::GROUP_ASSOCIATION_SUFFIX);
            $isProductAssociationPattern = sprintf('/^\w+%s$/', AssociationColumnsResolver::PRODUCT_ASSOCIATION_SUFFIX);
            $isProductModelAssociationPattern = sprintf('/^\w+%s$/', AssociationColumnsResolver::PRODUCT_MODEL_ASSOCIATION_SUFFIX);
            $isProductAssociationQuantityPattern = sprintf('/^\w+%s%s$/', AssociationColumnsResolver::PRODUCT_ASSOCIATION_SUFFIX, AssociationColumnsResolver::QUANTITY_SUFFIX);
            $isProductModelAssociationQuantityPattern = sprintf('/^\w+%s%s$/', AssociationColumnsResolver::PRODUCT_MODEL_ASSOCIATION_SUFFIX, AssociationColumnsResolver::QUANTITY_SUFFIX);
            foreach (array_keys($mappedItem) as $field) {
                $field = is_int($field) ? strval($field) : $field;
                $isGroup = (1 === preg_match($isGroupAssociationPattern, $field));
                $isProduct = (1 === preg_match($isProductAssociationPattern, $field));
                $isProductModel = (1 === preg_match($isProductModelAssociationPattern, $field));
                $isProductQuantity = (1 === preg_match($isProductAssociationQuantityPattern, $field));
                $isProductModelQuantity = (1 === preg_match($isProductModelAssociationQuantityPattern, $field));
                if ($isGroup || $isProduct || $isProductModel || $isProductQuantity || $isProductModelQuantity) {
                    unset($mappedItem[$field]);
                }
            }
        }

        return $mappedItem;
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
            $this->attributeColumnsResolver->resolveAttributeColumns(),
            $this->getOptionalAssociationFields()
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
     * Returns associations and quantified associations fields (resolves once)
     */
    protected function getOptionalAssociationFields(): array
    {
        if (empty($this->optionalAssocFields)) {
            $this->optionalAssocFields = array_merge(
                $this->assocColumnsResolver->resolveAssociationColumns(),
                $this->assocColumnsResolver->resolveQuantifiedAssociationColumns()
            );
        }

        return $this->optionalAssocFields;
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
