<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\HeaderFlatTranslator;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AttributeColumnInfoExtractor;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AttributeColumnsResolver;
use Akeneo\Tool\Component\Localization\LabelTranslatorInterface;
use Symfony\Component\Intl\Intl;

class AttributeHeaderFlatTranslator implements HeaderFlatTranslatorInterface
{
    /**
     * @var LabelTranslatorInterface
     */
    private $labelTranslator;
    /**
     * @var AttributeColumnsResolver
     */
    private $attributeColumnsResolver;
    /**
     * @var AttributeColumnInfoExtractor
     */
    private $attributeColumnInfoExtractor;

    public function __construct(
        LabelTranslatorInterface $labelTranslator,
        AttributeColumnsResolver $attributeColumnsResolver,
        AttributeColumnInfoExtractor $attributeColumnInfoExtractor
    ) {
        $this->labelTranslator = $labelTranslator;
        $this->attributeColumnInfoExtractor = $attributeColumnInfoExtractor;
        $this->attributeColumnsResolver = $attributeColumnsResolver;
    }

    public function supports(string $columnName): bool
    {
        $attributeColumns = $this->attributeColumnsResolver->resolveAttributeColumns();

        return in_array($columnName, $attributeColumns);
    }

    public function translate(string $columnName, string $locale, HeaderTranslationContext $context)
    {
        $columnInformations = $this->attributeColumnInfoExtractor->extractColumnInfo($columnName);
        $attribute = $columnInformations['attribute'];
        $attributeCode = $attribute->getCode();

        $columnLabelized = $context->getAttributeTranslation($attributeCode)?: sprintf('[%s]', $attributeCode);

        $extraInformation = [];
        if ($attribute->isLocalizable()) {
            $extraInformation[] = $columnInformations['locale_code'];
        }

        if ($attribute->isScopable()) {
            $extraInformation[] = $columnInformations['scope_code'];
        }

        if (!empty($extraInformation)) {
            $columnLabelized = $columnLabelized . " (".implode(', ', $extraInformation) . ")";
        }

        if ($attribute->getType() === 'pim_catalog_price_collection') {
            $language = \Locale::getPrimaryLanguage($locale);
            $currencyLabelized = Intl::getCurrencyBundle()->getCurrencyName(
                $columnInformations['price_currency'],
                $language
            );

            $columnLabelized = sprintf('%s (%s)', $columnLabelized, $currencyLabelized);
        } elseif ($attribute->getType() === 'pim_catalog_metric' && strpos($columnName, '-unit') !== false) {
            $metricLabelized = $this->labelTranslator->translate('pim_common.unit', $locale, '[unit]');

            $columnLabelized = sprintf('%s (%s)', $columnLabelized, $metricLabelized);
        }

        return $columnLabelized;
    }
}
