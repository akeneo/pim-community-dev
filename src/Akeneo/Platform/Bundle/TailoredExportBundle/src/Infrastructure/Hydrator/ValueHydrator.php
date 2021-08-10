<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Infrastructure\Hydrator;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductPriceInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValue;
use Akeneo\Platform\TailoredExport\Domain\Model\Source\AssociationTypeSource;
use Akeneo\Platform\TailoredExport\Domain\Model\Source\AttributeSource;
use Akeneo\Platform\TailoredExport\Domain\Model\Source\PropertySource;
use Akeneo\Platform\TailoredExport\Domain\Model\Source\SourceInterface;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\AssetCollectionValue;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\BooleanValue;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\CategoriesValue;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\DateValue;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\EnabledValue;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\FamilyValue;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\FamilyVariantValue;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\FileValue;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\GroupsValue;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\MeasurementValue;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\MultiSelectValue;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\NullValue;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\NumberValue;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\ParentValue;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\Price;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\PriceCollectionValue;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\ReferenceEntityCollectionValue;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\ReferenceEntityValue;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\SimpleSelectValue;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\SourceValueInterface;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\StringValue;
use Akeneo\Platform\TailoredExport\Infrastructure\Hydrator\Value\AssociationTypeValueHydrator;

class ValueHydrator
{
    private AssociationTypeValueHydrator $associationTypeValueHydrator;

    public function __construct(AssociationTypeValueHydrator $associationTypeValueHydrator)
    {
        $this->associationTypeValueHydrator = $associationTypeValueHydrator;
    }

    public function hydrate(
        ProductInterface $product,
        SourceInterface $source
    ): SourceValueInterface {
        if ($source instanceof AttributeSource) {
            $value = $product->getValue($source->getCode(), $source->getLocale(), $source->getChannel());

            return $this->hydrateFromAttribute($value, $source->getAttributeType(), $product);
        } elseif ($source instanceof PropertySource) {
            return $this->hydrateFromProperty($source->getName(), $product);
        } elseif ($source instanceof AssociationTypeSource) {
            return $this->associationTypeValueHydrator->hydrateFromSource($product, $source);
        } else {
            throw new \InvalidArgumentException(sprintf('Unsupported source type "%s"', get_class($source)));
        }
    }

    private function hydrateFromAttribute(
        ?ValueInterface $value,
        string $attributeType,
        ProductInterface $product
    ): SourceValueInterface {
        if (null === $value) {
            return new NullValue();
        }

        $data = $value->getData();

        if (null === $data) {
            return new NullValue();
        }

        switch ($attributeType) {
            case 'pim_catalog_asset_collection':
                return new AssetCollectionValue(
                    array_map('strval', $data),
                    $product->getIdentifier(),
                    $value->getScopeCode(),
                    $value->getLocaleCode()
                );
            case 'pim_catalog_file':
            case 'pim_catalog_image':
                return new FileValue(
                    $product->getIdentifier(),
                    $data->getStorage(),
                    $data->getKey(),
                    $data->getOriginalFilename(),
                    $value->getScopeCode(),
                    $value->getLocaleCode()
                );
            case 'pim_catalog_boolean':
                return new BooleanValue($data);
            case 'pim_catalog_date':
                return new DateValue($data);
            case 'pim_catalog_identifier':
            case 'pim_catalog_textarea':
            case 'pim_catalog_text':
                return new StringValue($data);
            case 'pim_catalog_metric':
                if (!$value instanceof MetricValue) {
                    throw new \LogicException('Malformed value for Measurement attribute');
                }

                return new MeasurementValue($value->getAmount(), $value->getUnit());
            case 'pim_catalog_number':
                return new NumberValue((string) $data);
            case 'pim_catalog_multiselect':
                return new MultiSelectValue($data);
            case 'pim_catalog_simpleselect':
                return new SimpleSelectValue($data);
            case 'pim_catalog_price_collection':
                return new PriceCollectionValue(array_map(
                    static fn (ProductPriceInterface $price) => new Price((string) $price->getData(), $price->getCurrency()),
                    $data->toArray()
                ));
            case 'akeneo_reference_entity':
                return new ReferenceEntityValue((string) $data);
            case 'akeneo_reference_entity_collection':
                return new ReferenceEntityCollectionValue(array_map('strval', $data));

            default:
                throw new \InvalidArgumentException(sprintf('Unsupported attribute type "%s"', $attributeType));
        }
    }

    private function hydrateFromProperty(
        string $propertyName,
        ProductInterface $product
    ): SourceValueInterface {
        switch ($propertyName) {
            case 'categories':
                return new CategoriesValue($product->getCategoryCodes());
            case 'enabled':
                return new EnabledValue($product->isEnabled());
            case 'family':
                $family = $product->getFamily();

                if (null === $family) {
                    return new NullValue();
                }

                return new FamilyValue($family->getCode());
            case 'family_variant':
                $familyVariant = $product->getFamilyVariant();

                if (null === $familyVariant) {
                    return new NullValue();
                }

                return new FamilyVariantValue($familyVariant->getCode());
            case 'groups':
                $groupCodes = $product->getGroupCodes();
                
                if (empty($groupCodes)) {
                    return new NullValue();
                }

                return new GroupsValue($groupCodes);
            case 'parent':
                $parent = $product->getParent();

                if (null === $parent) {
                    return new NullValue();
                }

                return new ParentValue($parent->getCode());
            default:
                throw new \LogicException(sprintf('Unsupported property name "%s"', $propertyName));
        }
    }
}
