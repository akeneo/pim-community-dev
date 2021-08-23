<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredExport\Infrastructure\Hydrator;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Association\AssociationType;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\AssetCollection\AssetCollectionCodeSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\AssetCollection\AssetCollectionLabelSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\Boolean\BooleanSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\Categories\CategoriesCodeSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\Categories\CategoriesLabelSelection;
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
use Akeneo\Platform\TailoredExport\Application\Common\Selection\Measurement\MeasurementValueSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\MultiSelect\MultiSelectCodeSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\MultiSelect\MultiSelectLabelSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\Number\NumberSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\Parent\ParentCodeSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\Parent\ParentLabelSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\PriceCollection\PriceCollectionAmountSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\PriceCollection\PriceCollectionCurrencyCodeSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\PriceCollection\PriceCollectionCurrencyLabelSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\QuantifiedAssociations\QuantifiedAssociationsCodeSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\QuantifiedAssociations\QuantifiedAssociationsLabelSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\QuantifiedAssociations\QuantifiedAssociationsQuantitySelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\ReferenceEntity\ReferenceEntityCodeSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\ReferenceEntity\ReferenceEntityLabelSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\ReferenceEntityCollection\ReferenceEntityCollectionCodeSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\ReferenceEntityCollection\ReferenceEntityCollectionLabelSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\Scalar\ScalarSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\SimpleAssociations\SimpleAssociationsCodeSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\SimpleAssociations\SimpleAssociationsGroupsLabelSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\SimpleAssociations\SimpleAssociationsLabelSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\SimpleAssociations\SimpleAssociationsSelectionInterface;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\SimpleSelect\SimpleSelectCodeSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\SimpleSelect\SimpleSelectLabelSelection;

class SelectionHydrator
{
    public function createPropertySelection(array $selectionConfiguration, string $propertyName): SelectionInterface
    {
        switch ($propertyName) {
            case 'categories':
                return $this->createCategoriesSelection($selectionConfiguration);
            case 'enabled':
                return new EnabledSelection();
            case 'family':
                return $this->createFamilySelection($selectionConfiguration);
            case 'family_variant':
                return $this->createFamilyVariantSelection($selectionConfiguration);
            case 'groups':
                return $this->createGroupsSelection($selectionConfiguration);
            case 'parent':
                return $this->createParentSelection($selectionConfiguration);
            default:
                throw new \LogicException(sprintf('Unsupported property name "%s"', $propertyName));
        }
    }

    public function createAttributeSelection(array $selectionConfiguration, Attribute $attribute): SelectionInterface
    {
        switch ($attribute->type()) {
            case 'pim_catalog_asset_collection':
                return $this->createAssetCollectionSelection($selectionConfiguration, $attribute);
            case 'pim_catalog_file':
            case 'pim_catalog_image':
                return $this->createFileSelection($selectionConfiguration, $attribute);
            case 'pim_catalog_boolean':
                return new BooleanSelection();
            case 'pim_catalog_date':
                return new DateSelection($selectionConfiguration['format']);
            case 'pim_catalog_identifier':
            case 'pim_catalog_textarea':
            case 'pim_catalog_text':
                return new ScalarSelection();
            case 'pim_catalog_metric':
                return $this->createMeasurementSelection($selectionConfiguration, $attribute);
            case 'pim_catalog_number':
                return new NumberSelection($selectionConfiguration['decimal_separator']);
            case 'pim_catalog_multiselect':
                return $this->createMultiselectSelection($selectionConfiguration, $attribute);
            case 'pim_catalog_simpleselect':
                return $this->createSimpleSelectSelection($selectionConfiguration, $attribute);
            case 'pim_catalog_price_collection':
                return $this->createPriceCollectionSelection($selectionConfiguration, $attribute);
            case 'akeneo_reference_entity':
                return $this->createReferenceEntitySelection($selectionConfiguration, $attribute);
            case 'akeneo_reference_entity_collection':
                return $this->createReferenceEntityCollectionSelection($selectionConfiguration, $attribute);
            default:
                throw new \LogicException(sprintf('Unsupported attribute type "%s"', $attribute->type()));
        }
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
        switch ($selectionConfiguration['type']) {
            case AssetCollectionCodeSelection::TYPE:
                return new AssetCollectionCodeSelection(
                    $selectionConfiguration['separator'],
                    $attribute->properties()['reference_data_name'],
                    $attribute->code()
                );
            case AssetCollectionLabelSelection::TYPE:
                return new AssetCollectionLabelSelection(
                    $selectionConfiguration['separator'],
                    $selectionConfiguration['locale'],
                    $attribute->properties()['reference_data_name'],
                    $attribute->code()
                );
            default:
                throw new \LogicException(
                    sprintf('Selection type "%s" is not supported for attribute type "%s"', $selectionConfiguration['type'], $attribute->type())
                );
        }
    }

    private function createFileSelection(array $selectionConfiguration, Attribute $attribute): SelectionInterface
    {
        switch ($selectionConfiguration['type']) {
            case FilePathSelection::TYPE:
                return new FilePathSelection($attribute->code());
            case FileKeySelection::TYPE:
                return new FileKeySelection($attribute->code());
            case FileNameSelection::TYPE:
                return new FileNameSelection($attribute->code());
            default:
                throw new \LogicException(
                    sprintf('Selection type "%s" is not supported for attribute type "%s"', $selectionConfiguration['type'], $attribute->type())
                );
        }
    }

    private function createMeasurementSelection(array $selectionConfiguration, Attribute $attribute): SelectionInterface
    {
        switch ($selectionConfiguration['type']) {
            case MeasurementValueSelection::TYPE:
                return new MeasurementValueSelection($selectionConfiguration['decimal_separator'] ?? '.');
            case MeasurementUnitCodeSelection::TYPE:
                return new MeasurementUnitCodeSelection();
            case MeasurementUnitLabelSelection::TYPE:
                return new MeasurementUnitLabelSelection(
                    $attribute->metricFamily(),
                    $selectionConfiguration['locale']
                );
            default:
                throw new \LogicException(
                    sprintf('Selection type "%s" is not supported for attribute type "%s"', $selectionConfiguration['type'], $attribute->type())
                );
        }
    }

    private function createMultiselectSelection(array $selectionConfiguration, Attribute $attribute): SelectionInterface
    {
        switch ($selectionConfiguration['type']) {
            case MultiSelectCodeSelection::TYPE:
                return new MultiSelectCodeSelection(
                    $selectionConfiguration['separator']
                );
            case MultiSelectLabelSelection::TYPE:
                return new MultiSelectLabelSelection(
                    $selectionConfiguration['separator'],
                    $selectionConfiguration['locale'],
                    $attribute->code()
                );
            default:
                throw new \LogicException(
                    sprintf('Selection type "%s" is not supported for attribute type "%s"', $selectionConfiguration['type'], $attribute->type())
                );
        }
    }

    private function createSimpleSelectSelection(array $selectionConfiguration, Attribute $attribute): SelectionInterface
    {
        switch ($selectionConfiguration['type']) {
            case SimpleSelectCodeSelection::TYPE:
                return new SimpleSelectCodeSelection();
            case SimpleSelectLabelSelection::TYPE:
                return new SimpleSelectLabelSelection(
                    $selectionConfiguration['locale'],
                    $attribute->code()
                );
            default:
                throw new \LogicException(
                    sprintf('Selection type "%s" is not supported for attribute type "%s"', $selectionConfiguration['type'], $attribute->type())
                );
        }
    }

    private function createPriceCollectionSelection(array $selectionConfiguration, Attribute $attribute): SelectionInterface
    {
        switch ($selectionConfiguration['type']) {
            case PriceCollectionCurrencyCodeSelection::TYPE:
                return new PriceCollectionCurrencyCodeSelection($selectionConfiguration['separator']);
            case PriceCollectionCurrencyLabelSelection::TYPE:
                return new PriceCollectionCurrencyLabelSelection($selectionConfiguration['separator'], $selectionConfiguration['locale']);
            case PriceCollectionAmountSelection::TYPE:
                return new PriceCollectionAmountSelection($selectionConfiguration['separator']);
            default:
                throw new \LogicException(
                    sprintf('Selection type "%s" is not supported for attribute type "%s"', $selectionConfiguration['type'], $attribute->type())
                );
        }
    }

    private function createReferenceEntitySelection(array $selectionConfiguration, Attribute $attribute): SelectionInterface
    {
        switch ($selectionConfiguration['type']) {
            case ReferenceEntityCodeSelection::TYPE:
                return new ReferenceEntityCodeSelection();
            case ReferenceEntityLabelSelection::TYPE:
                return new ReferenceEntityLabelSelection(
                    $selectionConfiguration['locale'],
                    $attribute->properties()['reference_data_name']
                );
            default:
                throw new \LogicException(
                    sprintf('Selection type "%s" is not supported for attribute type "%s"', $selectionConfiguration['type'], $attribute->type())
                );
        }
    }

    private function createReferenceEntityCollectionSelection(array $selectionConfiguration, Attribute $attribute): SelectionInterface
    {
        switch ($selectionConfiguration['type']) {
            case ReferenceEntityCollectionCodeSelection::TYPE:
                return new ReferenceEntityCollectionCodeSelection(
                    $selectionConfiguration['separator']
                );
            case ReferenceEntityCollectionLabelSelection::TYPE:
                return new ReferenceEntityCollectionLabelSelection(
                    $selectionConfiguration['separator'],
                    $selectionConfiguration['locale'],
                    $attribute->properties()['reference_data_name']
                );
            default:
                throw new \LogicException(
                    sprintf('Selection type "%s" is not supported for attribute type "%s"', $selectionConfiguration['type'], $attribute->type())
                );
        }
    }

    private function createCategoriesSelection(array $selectionConfiguration): SelectionInterface
    {
        switch ($selectionConfiguration['type']) {
            case CategoriesCodeSelection::TYPE:
                return new CategoriesCodeSelection(
                    $selectionConfiguration['separator']
                );
            case CategoriesLabelSelection::TYPE:
                return new CategoriesLabelSelection(
                    $selectionConfiguration['separator'],
                    $selectionConfiguration['locale']
                );
            default:
                throw new \LogicException(
                    sprintf('Selection type "%s" is not supported for Categories property', $selectionConfiguration['type'])
                );
        }
    }

    private function createFamilySelection(array $selectionConfiguration): SelectionInterface
    {
        switch ($selectionConfiguration['type']) {
            case FamilyCodeSelection::TYPE:
                return new FamilyCodeSelection();
            case FamilyLabelSelection::TYPE:
                return new FamilyLabelSelection($selectionConfiguration['locale']);
            default:
                throw new \LogicException(
                    sprintf('Selection type "%s" is not supported for Family property', $selectionConfiguration['type'])
                );
        }
    }

    private function createFamilyVariantSelection(array $selectionConfiguration): SelectionInterface
    {
        switch ($selectionConfiguration['type']) {
            case FamilyVariantCodeSelection::TYPE:
                return new FamilyVariantCodeSelection();
            case FamilyVariantLabelSelection::TYPE:
                return new FamilyVariantLabelSelection($selectionConfiguration['locale']);
            default:
                throw new \LogicException(
                    sprintf('Selection type "%s" is not supported for Family variant property', $selectionConfiguration['type'])
                );
        }
    }

    private function createGroupsSelection(array $selectionConfiguration): SelectionInterface
    {
        switch ($selectionConfiguration['type']) {
            case GroupsCodeSelection::TYPE:
                return new GroupsCodeSelection(
                    $selectionConfiguration['separator']
                );
            case GroupsLabelSelection::TYPE:
                return new GroupsLabelSelection(
                    $selectionConfiguration['separator'],
                    $selectionConfiguration['locale']
                );
            default:
                throw new \LogicException(
                    sprintf('Selection type "%s" is not supported for Groups property', $selectionConfiguration['type'])
                );
        }
    }

    private function createParentSelection(array $selectionConfiguration): SelectionInterface
    {
        switch ($selectionConfiguration['type']) {
            case ParentCodeSelection::TYPE:
                return new ParentCodeSelection();
            case ParentLabelSelection::TYPE:
                return new ParentLabelSelection(
                    $selectionConfiguration['locale'],
                    $selectionConfiguration['channel']
                );
            default:
                throw new \LogicException(
                    sprintf('Selection type "%s" is not supported for Parent property', $selectionConfiguration['type'])
                );
        }
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
                        $selectionConfiguration['separator']
                    );
                }

                return new SimpleAssociationsLabelSelection(
                    $entityType,
                    $selectionConfiguration['channel'],
                    $selectionConfiguration['locale'],
                    $selectionConfiguration['separator']
                );
            default:
                throw new \LogicException(
                    sprintf('Selection type "%s" is not supported for SimpleAssociation', $selectionConfiguration['type'])
                );
        }
    }

    private function createQuantifiedAssociationsSelection(array $selectionConfiguration): SelectionInterface
    {
        switch ($selectionConfiguration['type']) {
            case QuantifiedAssociationsCodeSelection::TYPE:
                return new QuantifiedAssociationsCodeSelection(
                    $selectionConfiguration['entity_type'],
                    $selectionConfiguration['separator']
                );
            case QuantifiedAssociationsLabelSelection::TYPE:
                return new QuantifiedAssociationsLabelSelection(
                    $selectionConfiguration['entity_type'],
                    $selectionConfiguration['channel'],
                    $selectionConfiguration['locale'],
                    $selectionConfiguration['separator']
                );
            case QuantifiedAssociationsQuantitySelection::TYPE:
                return new QuantifiedAssociationsQuantitySelection(
                    $selectionConfiguration['entity_type'],
                    $selectionConfiguration['separator']
                );
            default:
                throw new \LogicException(
                    sprintf('Selection type "%s" is not supported for QuantifiedAssociation', $selectionConfiguration['type'])
                );
        }
    }
}
