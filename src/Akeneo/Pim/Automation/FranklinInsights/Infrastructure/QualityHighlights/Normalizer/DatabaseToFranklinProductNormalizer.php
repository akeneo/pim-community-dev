<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\QualityHighlights\Normalizer;

use Akeneo\Pim\Automation\FranklinInsights\Application\QualityHighlights\Normalizer\ProductNormalizerInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Model\Read\Attribute;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Model\Read\Product;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Query\SelectSupportedAttributesByFamilyQueryInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;

class DatabaseToFranklinProductNormalizer implements ProductNormalizerInterface
{
    /** @var SelectSupportedAttributesByFamilyQueryInterface */
    private $selectAttributesByFamilyQuery;

    public function __construct(SelectSupportedAttributesByFamilyQueryInterface $selectAttributesByFamilyQuery)
    {
        $this->selectAttributesByFamilyQuery = $selectAttributesByFamilyQuery;
    }

    public function normalize(Product $product): array
    {
        return [
            'id' => $product->getId()->__toString(),
            'family' => $product->getFamilyCode()->__toString(),
            'attributes' => $this->normalizeProductValues($product),
        ];
    }

    private function normalizeProductValues(Product $product): array
    {
        $attributes = $this->selectAttributesByFamilyQuery->execute($product->getFamilyCode());
        $rawProductValues = array_intersect_key($product->getRawValues(), $attributes);

        $normalizedProductValues = [];
        foreach ($rawProductValues as $attributeCode => $rawValuesByChannel) {
            $normalizedAttributeValues = [];
            foreach ($rawValuesByChannel as $channel => $rawValuesByLocale) {
                foreach ($this->filterProductValuesBySupportedLocales($rawValuesByLocale) as $locale => $rawValue) {
                    $normalizedAttributeValues[] = [
                        'value' => $this->normalizeProductValue($rawValue, $attributes[$attributeCode]),
                        'locale' => $locale === '<all_locales>' ? null : $locale,
                        'channel' => $channel === '<all_channels>' ? null : $channel,
                    ];
                }
            }

            if (!empty($normalizedAttributeValues)) {
                $normalizedProductValues[$attributeCode] = $normalizedAttributeValues;
            }
        }

        return $normalizedProductValues;
    }

    private function filterProductValuesBySupportedLocales(array $productValues): array
    {
        return array_filter($productValues, function ($locale) {
            return $locale === '<all_locales>' || strpos($locale, 'en_') === 0;
        }, ARRAY_FILTER_USE_KEY);
    }

    private function normalizeProductValue($productValue, Attribute $attribute)
    {
        $attributeTye = $attribute->getType()->__toString();

        switch ($attributeTye) {
            case AttributeTypes::TEXT:
            case AttributeTypes::TEXTAREA:
            case AttributeTypes::NUMBER:
            case AttributeTypes::OPTION_SIMPLE_SELECT:
                $normalizedValue = (string) $productValue;
                break;

            case AttributeTypes::BOOLEAN:
                $normalizedValue = true === $productValue ? 'Yes' : 'No';
                break;

            case AttributeTypes::OPTION_MULTI_SELECT:
                $normalizedValue = is_array($productValue) ? implode(',', $productValue) : '';
                break;

            case AttributeTypes::METRIC:
                $normalizedValue = trim(sprintf('%s %s', $productValue['amount'] ?? '', $productValue['unit'] ?? ''));
                break;

            default:
                throw new \LogicException(sprintf('The attribute "%s" of type "%s" is not supported.', $attribute->getCode(), $attributeTye));
        }

        return $normalizedValue;
    }
}
