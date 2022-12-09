<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredExport\Infrastructure\Hydrator;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Association\AssociationType;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\AssetCollection\AssetCollectionCodeSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\AssetCollection\AssetCollectionLabelSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\AssetCollection\AssetCollectionMediaFileSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\AssetCollection\AssetCollectionMediaLinkSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\Boolean\BooleanSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\Categories\CategoriesCodeSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\Categories\CategoriesLabelSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\Code\CodeSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\Date\DateSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\Enabled\EnabledSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\Family\FamilyCodeSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\Family\FamilyLabelSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\FamilyVariant\FamilyVariantCodeSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\FamilyVariant\FamilyVariantLabelSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\File\FileKeySelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\File\FileNameSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\File\FilePathSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\Groups\GroupsCodeSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\Groups\GroupsLabelSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\Measurement\MeasurementUnitCodeSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\Measurement\MeasurementUnitLabelSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\Measurement\MeasurementUnitSymbolSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\Measurement\MeasurementValueAndUnitLabelSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\Measurement\MeasurementValueAndUnitSymbolSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\Measurement\MeasurementValueSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\MultiSelect\MultiSelectCodeSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\MultiSelect\MultiSelectLabelSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\Number\NumberSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\Parent\ParentCodeSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\Parent\ParentLabelSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\PriceCollection\PriceCollectionAmountSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\PriceCollection\PriceCollectionCurrencyCodeSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\PriceCollection\PriceCollectionCurrencyLabelSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\QualityScore\QualityScoreCodeSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\QuantifiedAssociations\QuantifiedAssociationsCodeSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\QuantifiedAssociations\QuantifiedAssociationsLabelSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\QuantifiedAssociations\QuantifiedAssociationsQuantitySelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\ReferenceEntity\ReferenceEntityAttributeSelectionInterface;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\ReferenceEntity\ReferenceEntityCodeSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\ReferenceEntity\ReferenceEntityLabelSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\ReferenceEntity\ReferenceEntityNumberAttributeSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\ReferenceEntity\ReferenceEntityTextAttributeSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\ReferenceEntityCollection\ReferenceEntityCollectionAttributeSelectionInterface;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\ReferenceEntityCollection\ReferenceEntityCollectionCodeSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\ReferenceEntityCollection\ReferenceEntityCollectionLabelSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\ReferenceEntityCollection\ReferenceEntityCollectionNumberAttributeSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\ReferenceEntityCollection\ReferenceEntityCollectionTextAttributeSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\Scalar\ScalarSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\SimpleAssociations\SimpleAssociationsCodeSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\SimpleAssociations\SimpleAssociationsGroupsLabelSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\SimpleAssociations\SimpleAssociationsLabelSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\SimpleAssociations\SimpleAssociationsSelectionInterface;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\SimpleSelect\SimpleSelectCodeSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\SimpleSelect\SimpleSelectLabelSelection;
use Akeneo\Platform\TailoredExport\Application\MapValues\SelectionApplier\Measurement\MeasurementApplierInterface;

class SelectionHydrator
{
    public function createPropertySelection(array $selectionConfiguration, string $propertyName): SelectionInterface
    {
        return match ($propertyName) {
            'code' => new CodeSelection(),
            'categories' => $this->createCategoriesSelection($selectionConfiguration),
            'enabled' => new EnabledSelection(),
            'family' => $this->createFamilySelection($selectionConfiguration),
            'family_variant' => $this->createFamilyVariantSelection($selectionConfiguration),
            'groups' => $this->createGroupsSelection($selectionConfiguration),
            'parent' => $this->createParentSelection($selectionConfiguration),
            'quality_score' => new QualityScoreCodeSelection(),
            default => throw new \LogicException(sprintf('Unsupported property name "%s"', $propertyName)),
        };
    }

    public function createAttributeSelection(array $selectionConfiguration, Attribute $attribute): SelectionInterface
    {
        return match ($attribute->type()) {
            'pim_catalog_asset_collection' => $this->createAssetCollectionSelection($selectionConfiguration, $attribute),
            'pim_catalog_file', 'pim_catalog_image' => $this->createFileSelection($selectionConfiguration, $attribute),
            'pim_catalog_boolean' => new BooleanSelection(),
            'pim_catalog_date' => new DateSelection($selectionConfiguration['format']),
            'pim_catalog_identifier', 'pim_catalog_textarea', 'pim_catalog_text', 'pim_catalog_table' => new ScalarSelection(),
            'pim_catalog_metric' => $this->createMeasurementSelection($selectionConfiguration, $attribute),
            'pim_catalog_number' => new NumberSelection($selectionConfiguration['decimal_separator']),
            'pim_catalog_multiselect' => $this->createMultiselectSelection($selectionConfiguration, $attribute),
            'pim_catalog_simpleselect' => $this->createSimpleSelectSelection($selectionConfiguration, $attribute),
            'pim_catalog_price_collection' => $this->createPriceCollectionSelection($selectionConfiguration, $attribute),
            'akeneo_reference_entity' => $this->createReferenceEntitySelection($selectionConfiguration, $attribute),
            'akeneo_reference_entity_collection' => $this->createReferenceEntityCollectionSelection($selectionConfiguration, $attribute),
            default => throw new \LogicException(sprintf('Unsupported attribute type "%s"', $attribute->type())),
        };
    }

    public function createAssociationSelection(array $selectionConfiguration, AssociationType $associationType): SelectionInterface
    {
        if ($associationType->isQuantified()) {
            return $this->createQuantifiedAssociationsSelection($selectionConfiguration);
        }

        return $this->createSimpleAssociationsSelection($selectionConfiguration);
    }

    private function createAssetCollectionSelection(array $selectionConfiguration, Attribute $attribute): SelectionInterface
    {
        return match ($selectionConfiguration['type']) {
            AssetCollectionCodeSelection::TYPE => new AssetCollectionCodeSelection(
                $selectionConfiguration['separator'],
                $attribute->properties()['reference_data_name'],
                $attribute->code(),
            ),
            AssetCollectionLabelSelection::TYPE => new AssetCollectionLabelSelection(
                $selectionConfiguration['separator'],
                $selectionConfiguration['locale'],
                $attribute->properties()['reference_data_name'],
                $attribute->code(),
            ),
            AssetCollectionMediaFileSelection::TYPE => new AssetCollectionMediaFileSelection(
                $selectionConfiguration['separator'],
                $selectionConfiguration['channel'],
                $selectionConfiguration['locale'],
                $attribute->properties()['reference_data_name'],
                $attribute->code(),
                $selectionConfiguration['property'],
            ),
            AssetCollectionMediaLinkSelection::TYPE => new AssetCollectionMediaLinkSelection(
                $selectionConfiguration['separator'],
                $selectionConfiguration['channel'],
                $selectionConfiguration['locale'],
                $attribute->properties()['reference_data_name'],
                $attribute->code(),
                $selectionConfiguration['with_prefix_and_suffix'],
            ),
            default => throw new \LogicException(sprintf('Selection type "%s" is not supported for attribute type "%s"', $selectionConfiguration['type'], $attribute->type())),
        };
    }

    private function createFileSelection(array $selectionConfiguration, Attribute $attribute): SelectionInterface
    {
        return match ($selectionConfiguration['type']) {
            FilePathSelection::TYPE => new FilePathSelection($attribute->code()),
            FileKeySelection::TYPE => new FileKeySelection($attribute->code()),
            FileNameSelection::TYPE => new FileNameSelection($attribute->code()),
            default => throw new \LogicException(sprintf('Selection type "%s" is not supported for attribute type "%s"', $selectionConfiguration['type'], $attribute->type())),
        };
    }

    private function createMeasurementSelection(array $selectionConfiguration, Attribute $attribute): SelectionInterface
    {
        return match ($selectionConfiguration['type']) {
            MeasurementValueSelection::TYPE => new MeasurementValueSelection(
                $selectionConfiguration['decimal_separator'] ?? MeasurementApplierInterface::DEFAULT_DECIMAL_SEPARATOR,
            ),
            MeasurementUnitCodeSelection::TYPE => new MeasurementUnitCodeSelection(),
            MeasurementUnitSymbolSelection::TYPE => new MeasurementUnitSymbolSelection(
                $attribute->metricFamily(),
            ),
            MeasurementUnitLabelSelection::TYPE => new MeasurementUnitLabelSelection(
                $attribute->metricFamily(),
                $selectionConfiguration['locale'],
            ),
            MeasurementValueAndUnitLabelSelection::TYPE => new MeasurementValueAndUnitLabelSelection(
                $selectionConfiguration['decimal_separator'] ?? MeasurementApplierInterface::DEFAULT_DECIMAL_SEPARATOR,
                $attribute->metricFamily(),
                $selectionConfiguration['locale'],
            ),
            MeasurementValueAndUnitSymbolSelection::TYPE => new MeasurementValueAndUnitSymbolSelection(
                $selectionConfiguration['decimal_separator'] ?? MeasurementApplierInterface::DEFAULT_DECIMAL_SEPARATOR,
                $attribute->metricFamily(),
            ),
            default => throw new \LogicException(sprintf('Selection type "%s" is not supported for attribute type "%s"', $selectionConfiguration['type'], $attribute->type())),
        };
    }

    private function createMultiselectSelection(array $selectionConfiguration, Attribute $attribute): SelectionInterface
    {
        return match ($selectionConfiguration['type']) {
            MultiSelectCodeSelection::TYPE => new MultiSelectCodeSelection(
                $selectionConfiguration['separator'],
            ),
            MultiSelectLabelSelection::TYPE => new MultiSelectLabelSelection(
                $selectionConfiguration['separator'],
                $selectionConfiguration['locale'],
                $attribute->code(),
            ),
            default => throw new \LogicException(sprintf('Selection type "%s" is not supported for attribute type "%s"', $selectionConfiguration['type'], $attribute->type())),
        };
    }

    private function createSimpleSelectSelection(array $selectionConfiguration, Attribute $attribute): SelectionInterface
    {
        return match ($selectionConfiguration['type']) {
            SimpleSelectCodeSelection::TYPE => new SimpleSelectCodeSelection(),
            SimpleSelectLabelSelection::TYPE => new SimpleSelectLabelSelection(
                $selectionConfiguration['locale'],
                $attribute->code(),
            ),
            default => throw new \LogicException(sprintf('Selection type "%s" is not supported for attribute type "%s"', $selectionConfiguration['type'], $attribute->type())),
        };
    }

    private function createPriceCollectionSelection(array $selectionConfiguration, Attribute $attribute): SelectionInterface
    {
        return match ($selectionConfiguration['type']) {
            PriceCollectionCurrencyCodeSelection::TYPE => new PriceCollectionCurrencyCodeSelection(
                $selectionConfiguration['separator'],
                $selectionConfiguration['currencies'] ?? [],
            ),
            PriceCollectionCurrencyLabelSelection::TYPE => new PriceCollectionCurrencyLabelSelection(
                $selectionConfiguration['separator'],
                $selectionConfiguration['locale'],
                $selectionConfiguration['currencies'] ?? [],
            ),
            PriceCollectionAmountSelection::TYPE => new PriceCollectionAmountSelection(
                $selectionConfiguration['separator'],
                $selectionConfiguration['currencies'] ?? [],
            ),
            default => throw new \LogicException(sprintf('Selection type "%s" is not supported for attribute type "%s"', $selectionConfiguration['type'], $attribute->type())),
        };
    }

    private function createReferenceEntitySelection(array $selectionConfiguration, Attribute $attribute): SelectionInterface
    {
        return match ($selectionConfiguration['type']) {
            ReferenceEntityCodeSelection::TYPE => new ReferenceEntityCodeSelection(),
            ReferenceEntityLabelSelection::TYPE => new ReferenceEntityLabelSelection(
                $selectionConfiguration['locale'],
                $attribute->properties()['reference_data_name'],
            ),
            ReferenceEntityAttributeSelectionInterface::TYPE => $this->createReferenceEntityAttributeSelection(
                $selectionConfiguration,
                $attribute->properties()['reference_data_name'],
            ),
            default => throw new \LogicException(sprintf('Selection type "%s" is not supported for attribute type "%s"', $selectionConfiguration['type'], $attribute->type())),
        };
    }

    private function createReferenceEntityAttributeSelection(array $selectionConfiguration, string $referenceEntityCode): SelectionInterface
    {
        return match ($selectionConfiguration['attribute_type']) {
            ReferenceEntityTextAttributeSelection::TYPE => new ReferenceEntityTextAttributeSelection(
                $referenceEntityCode,
                $selectionConfiguration['attribute_identifier'],
                $selectionConfiguration['channel'],
                $selectionConfiguration['locale'],
            ),
            ReferenceEntityNumberAttributeSelection::TYPE => new ReferenceEntityNumberAttributeSelection(
                $referenceEntityCode,
                $selectionConfiguration['attribute_identifier'],
                $selectionConfiguration['decimal_separator'],
                $selectionConfiguration['channel'],
                $selectionConfiguration['locale'],
            ),
            default => throw new \LogicException(sprintf('Selection type "%s" is not supported for Reference Entity attribute', $selectionConfiguration['attribute_type'])),
        };
    }

    private function createReferenceEntityCollectionSelection(array $selectionConfiguration, Attribute $attribute): SelectionInterface
    {
        return match ($selectionConfiguration['type']) {
            ReferenceEntityCollectionCodeSelection::TYPE => new ReferenceEntityCollectionCodeSelection(
                $selectionConfiguration['separator'],
            ),
            ReferenceEntityCollectionLabelSelection::TYPE => new ReferenceEntityCollectionLabelSelection(
                $selectionConfiguration['separator'],
                $selectionConfiguration['locale'],
                $attribute->properties()['reference_data_name'],
            ),
            ReferenceEntityCollectionAttributeSelectionInterface::TYPE => $this->createReferenceEntityCollectionAttributeSelection(
                $selectionConfiguration,
                $attribute->properties()['reference_data_name'],
            ),
            default => throw new \LogicException(sprintf('Selection type "%s" is not supported for attribute type "%s"', $selectionConfiguration['type'], $attribute->type())),
        };
    }

    private function createReferenceEntityCollectionAttributeSelection(array $selectionConfiguration, string $referenceEntityCode): SelectionInterface
    {
        return match ($selectionConfiguration['attribute_type']) {
            ReferenceEntityCollectionTextAttributeSelection::TYPE => new ReferenceEntityCollectionTextAttributeSelection(
                $selectionConfiguration['separator'],
                $referenceEntityCode,
                $selectionConfiguration['attribute_identifier'],
                $selectionConfiguration['channel'],
                $selectionConfiguration['locale'],
            ),
            ReferenceEntityNumberAttributeSelection::TYPE => new ReferenceEntityCollectionNumberAttributeSelection(
                $selectionConfiguration['separator'],
                $referenceEntityCode,
                $selectionConfiguration['attribute_identifier'],
                $selectionConfiguration['decimal_separator'],
                $selectionConfiguration['channel'],
                $selectionConfiguration['locale'],
            ),
            default => throw new \LogicException(sprintf('Selection type "%s" is not supported for Reference Entity Collection attribute', $selectionConfiguration['attribute_type'])),
        };
    }

    private function createCategoriesSelection(array $selectionConfiguration): SelectionInterface
    {
        return match ($selectionConfiguration['type']) {
            CategoriesCodeSelection::TYPE => new CategoriesCodeSelection(
                $selectionConfiguration['separator'],
            ),
            CategoriesLabelSelection::TYPE => new CategoriesLabelSelection(
                $selectionConfiguration['separator'],
                $selectionConfiguration['locale'],
            ),
            default => throw new \LogicException(sprintf('Selection type "%s" is not supported for Categories property', $selectionConfiguration['type'])),
        };
    }

    private function createFamilySelection(array $selectionConfiguration): SelectionInterface
    {
        return match ($selectionConfiguration['type']) {
            FamilyCodeSelection::TYPE => new FamilyCodeSelection(),
            FamilyLabelSelection::TYPE => new FamilyLabelSelection($selectionConfiguration['locale']),
            default => throw new \LogicException(sprintf('Selection type "%s" is not supported for Family property', $selectionConfiguration['type'])),
        };
    }

    private function createFamilyVariantSelection(array $selectionConfiguration): SelectionInterface
    {
        return match ($selectionConfiguration['type']) {
            FamilyVariantCodeSelection::TYPE => new FamilyVariantCodeSelection(),
            FamilyVariantLabelSelection::TYPE => new FamilyVariantLabelSelection($selectionConfiguration['locale']),
            default => throw new \LogicException(sprintf('Selection type "%s" is not supported for Family variant property', $selectionConfiguration['type'])),
        };
    }

    private function createGroupsSelection(array $selectionConfiguration): SelectionInterface
    {
        return match ($selectionConfiguration['type']) {
            GroupsCodeSelection::TYPE => new GroupsCodeSelection(
                $selectionConfiguration['separator'],
            ),
            GroupsLabelSelection::TYPE => new GroupsLabelSelection(
                $selectionConfiguration['separator'],
                $selectionConfiguration['locale'],
            ),
            default => throw new \LogicException(sprintf('Selection type "%s" is not supported for Groups property', $selectionConfiguration['type'])),
        };
    }

    private function createParentSelection(array $selectionConfiguration): SelectionInterface
    {
        return match ($selectionConfiguration['type']) {
            ParentCodeSelection::TYPE => new ParentCodeSelection(),
            ParentLabelSelection::TYPE => new ParentLabelSelection(
                $selectionConfiguration['locale'],
                $selectionConfiguration['channel'],
            ),
            default => throw new \LogicException(sprintf('Selection type "%s" is not supported for Parent property', $selectionConfiguration['type'])),
        };
    }

    private function createSimpleAssociationsSelection(array $selectionConfiguration): SelectionInterface
    {
        $entityType = $selectionConfiguration['entity_type'];
        switch ($selectionConfiguration['type']) {
            case SimpleAssociationsCodeSelection::TYPE:
                return new SimpleAssociationsCodeSelection($entityType, $selectionConfiguration['separator']);
            case SimpleAssociationsLabelSelection::TYPE:
                if (SimpleAssociationsSelectionInterface::ENTITY_TYPE_GROUPS === $entityType) {
                    return new SimpleAssociationsGroupsLabelSelection(
                        $selectionConfiguration['locale'],
                        $selectionConfiguration['separator'],
                    );
                }

                return new SimpleAssociationsLabelSelection(
                    $entityType,
                    $selectionConfiguration['channel'],
                    $selectionConfiguration['locale'],
                    $selectionConfiguration['separator'],
                );
            default:
                throw new \LogicException(sprintf('Selection type "%s" is not supported for SimpleAssociation', $selectionConfiguration['type']));
        }
    }

    private function createQuantifiedAssociationsSelection(array $selectionConfiguration): SelectionInterface
    {
        return match ($selectionConfiguration['type']) {
            QuantifiedAssociationsCodeSelection::TYPE => new QuantifiedAssociationsCodeSelection(
                $selectionConfiguration['entity_type'],
                $selectionConfiguration['separator'],
            ),
            QuantifiedAssociationsLabelSelection::TYPE => new QuantifiedAssociationsLabelSelection(
                $selectionConfiguration['entity_type'],
                $selectionConfiguration['channel'],
                $selectionConfiguration['locale'],
                $selectionConfiguration['separator'],
            ),
            QuantifiedAssociationsQuantitySelection::TYPE => new QuantifiedAssociationsQuantitySelection(
                $selectionConfiguration['entity_type'],
                $selectionConfiguration['separator'],
            ),
            default => throw new \LogicException(sprintf('Selection type "%s" is not supported for QuantifiedAssociation', $selectionConfiguration['type'])),
        };
    }
}
