<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat;

use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\AssociationType\SqlGetAssociationTypeLabels;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Product\ProductValueConverter;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\ArrayConverter\StandardToFlat\AbstractSimpleArrayConverter;
use Akeneo\Tool\Component\Localization\LabelTranslator;

/**
 * Convert standard format to flat format for product
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ProductWithLabel extends AbstractSimpleArrayConverter implements ArrayConverterInterface
{
    /** @var ProductValueConverter */
    protected $valueConverter;

    private $associationTypeLabels;

    private $translator;

    /**
     * @param ProductValueConverter $valueConverter
     */
    public function __construct(ProductValueConverter $valueConverter, SqlGetAssociationTypeLabels $associationTypeLabels, LabelTranslator $translator)
    {
        $this->valueConverter = $valueConverter;
        $this->associationTypeLabels = $associationTypeLabels;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    protected function convertProperty($property, $data, array $convertedItem, array $options)
    {
        $labelLocale = $options['label_locale'];

        switch ($property) {
            case 'associations':
                $convertedItem = $this->convertAssociations($data, $convertedItem, $labelLocale);
                break;
            case 'quantified_associations':
                $convertedItem = $this->convertQuantifiedAssociations($data, $convertedItem, $labelLocale);
                break;
            case 'categories':
                $categoryLabel = $this->translator->trans('pim_common.categories', [], null, $labelLocale, '[categories]');
                $convertedItem[$categoryLabel] = implode(',', $data);
                break;
            case 'enabled':
                $enabledLabel = $this->translator->trans('pim_common.enabled', [], null, $labelLocale, '[enabled]');
                $convertedItem[$enabledLabel] = false === $data || null === $data ? $this->translator->trans('pim_common.no', [], null, $labelLocale, '[no]') : $this->translator->trans('pim_common.yes', [], null, $labelLocale, '[yes]');
                break;
            case 'family':
                $familyLabel = $this->translator->trans('pim_common.family', [], null, $labelLocale, '[family]');
                $convertedItem[$familyLabel] = (string) $data;
                break;
            case 'parent':
                if (null !== $data && '' !== $data) {
                    $parentLabel = $this->translator->trans('pim_common.parent', [], null, $labelLocale, '[parent]');
                    $convertedItem[$parentLabel] = (string) $data;
                }
                break;
            case 'groups':
                $groupLabel = $this->translator->trans('pim_common.groups', [], null, $labelLocale, '[groups]');

                $convertedItem[$groupLabel] = is_array($data) ? implode(',', $data) : (string) $data;
                break;
            case 'values':
                foreach ($data as $code => $attribute) {
                    $convertedItem = $convertedItem + $this->valueConverter->convertAttributeWithLabel($code, $labelLocale, $attribute);
                }
                break;
            case 'identifier':
            case 'created':
            case 'updated':
            case 'family_code':
                break;
            default:
                $convertedItem = $convertedItem + $this->valueConverter->convertAttributeWithLabel($property, $labelLocale, $data);
        }

        return $convertedItem;
    }

    private function convertAssociations(array $data, array $convertedItem, string $labelLocale): array
    {
        $associationTypeLabels = $this->associationTypeLabels->forAssociationTypeCodes(array_keys($data));
        foreach ($data as $associationTypeCode => $associations) {
            foreach ($associations as $entityType => $entities) {
                $entityTypeLabel = $this->translator->trans("pim_common.$entityType", [], null, $labelLocale, "[$entityType]");
                $associationTypeLabel = $associationTypeLabels[$associationTypeCode][$labelLocale] ?? "[$associationTypeCode]";

                $propertyName = sprintf('%s %s', $associationTypeLabel, $entityTypeLabel);
                $convertedItem[$propertyName] = implode(',', $entities);
            }
        }

        return $convertedItem;
    }

    private function convertQuantifiedAssociations(array $data, array $convertedItem, string $labelLocale): array
    {
        $associationTypeLabels = $this->associationTypeLabels->forAssociationTypeCodes(array_keys($data));
        $quantityLabel = $this->translator->trans('pim_common.quantity', [], null, $labelLocale, '[quantity]');

        foreach ($data as $associationTypeCode => $quantifiedAssociations) {
            foreach ($quantifiedAssociations as $entityType => $quantifiedLinks) {
                $entityTypeLabel = $this->translator->trans("pim_common.$entityType", [], null, $labelLocale, "[$entityType]");
                $associationTypeLabel = $associationTypeLabels[$associationTypeCode][$labelLocale] ?? "[$associationTypeCode]";
                $propertyName = sprintf('%s %s', $associationTypeLabel, $entityTypeLabel);

                $convertedItem[$propertyName] = implode(',', array_column($quantifiedLinks, 'identifier'));
                $convertedItem[sprintf('%s (%s)', $propertyName, $quantityLabel)] = implode('|', array_column($quantifiedLinks, 'quantity'));
            }
        }

        return $convertedItem;
    }
}
