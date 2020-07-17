<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat;

use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\AssociationType\SqlGetAssociationTypeLabels;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Product\ProductValueConverter;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\ArrayConverter\StandardToFlat\AbstractSimpleArrayConverter;
use Symfony\Component\Translation\TranslatorInterface;

class ProductModelWithLabel extends AbstractSimpleArrayConverter implements ArrayConverterInterface
{
    /** @var ProductValueConverter */
    protected $valueConverter;
    /**
     * @var SqlGetAssociationTypeLabels
     */
    private $associationTypeLabels;
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @param ProductValueConverter $valueConverter
     */
    public function __construct(
        ProductValueConverter $valueConverter,
        SqlGetAssociationTypeLabels $associationTypeLabels,
        TranslatorInterface $translator
    ) {
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
                $categoryLabel = $this->translator->trans('pim_common.categories', [], null, $labelLocale);

                $convertedItem[$categoryLabel] = implode(',', $data);
                break;
            case 'code':
            case 'family_variant':
            case 'parent':
                $propertyLabel = $this->translator->trans("pim_common.$property", [], null, $labelLocale);

                $convertedItem[$propertyLabel] = (string) $data;
                break;
            case 'values':
                foreach ($data as $code => $attribute) {
                    $convertedItem = $convertedItem + $this->valueConverter->convertAttributeWithLabel($code, $labelLocale, $attribute);
                }
                break;
            case 'created':
            case 'updated':
            default:
                break;
        }

        return $convertedItem;
    }

    private function convertAssociations(array $data, array $convertedItem, string $labelLocale)
    {
        $associationTypeLabels = $this->associationTypeLabels->forAssociationTypeCodes(array_keys($data));
        foreach ($data as $associationTypeCode => $associations) {
            foreach ($associations as $entityType => $entities) {
                $entityTypeLabel = $this->translator->trans("pim_common.$entityType", [], null, $labelLocale);
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
        $quantityLabel = $this->translator->trans('pim_common.quantity', [], null, $labelLocale);

        foreach ($data as $associationTypeCode => $quantifiedAssociations) {
            foreach ($quantifiedAssociations as $entityType => $quantifiedLinks) {
                $entityTypeLabel = $this->translator->trans("pim_common.$entityType", [], null, $labelLocale);
                $associationTypeLabel = $associationTypeLabels[$associationTypeCode][$labelLocale] ?? "[$entityType]";
                $propertyName = sprintf('%s %s', $associationTypeLabel, $entityTypeLabel);

                $convertedItem[$propertyName] = implode(',', array_column($quantifiedLinks, 'identifier'));
                $convertedItem[sprintf('%s (%s)', $propertyName, $quantityLabel)] = implode('|', array_column($quantifiedLinks, 'quantity'));
            }
        }

        return $convertedItem;
    }
}
