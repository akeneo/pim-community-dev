<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Proposal\Normalizer\Standard;

use Akeneo\Pim\Automation\FranklinInsights\Application\Proposal\Normalizer\SuggestedDataNormalizerInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\FamilyAttribute\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\ValueObject\SuggestedData;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\ValueObject\SuggestedValue;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Proposal\Normalizer\Standard\SuggestedValue\BooleanNormalizer;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Proposal\Normalizer\Standard\SuggestedValue\MetricNormalizer;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Proposal\Normalizer\Standard\SuggestedValue\MultiSelectNormalizer;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Proposal\Normalizer\Standard\SuggestedValue\NumberNormalizer;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Proposal\Normalizer\Standard\SuggestedValue\SimpleSelectNormalizer;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Proposal\Normalizer\Standard\SuggestedValue\TextNormalizer;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Repository\AttributeOptionRepositoryInterface;
use Akeneo\Tool\Bundle\MeasureBundle\Convert\MeasureConverter;

/**
 * Normalizes all valid suggested values from a SuggestedData object into Akeneo standard format.
 * If suggested value is invalid (like a text for a number attribute type, or a metric without unit),
 * it will be skipped.
 *
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class SuggestedDataNormalizer implements SuggestedDataNormalizerInterface
{
    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var AttributeOptionRepositoryInterface */
    private $attributeOptionRepository;

    /** @var MeasureConverter */
    private $measureConverter;

    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        AttributeOptionRepositoryInterface $attributeOptionRepository,
        MeasureConverter $measureConverter
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->attributeOptionRepository = $attributeOptionRepository;
        $this->measureConverter = $measureConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize(SuggestedData $suggestedData): array
    {
        $normalizedValues = [];
        $attributeCodes = [];
        foreach ($suggestedData as $suggestedValue) {
            $attributeCodes[] = $suggestedValue->pimAttributeCode();
        }
        $attributeTypes = $this->attributeRepository->getAttributeTypeByCodes($attributeCodes);

        foreach ($suggestedData as $suggestedValue) {
            $attributeCode = $suggestedValue->pimAttributeCode();
            if (isset($attributeTypes[$attributeCode])) {
                $normalizedValues += $this->normalizeValue($attributeTypes[$attributeCode], $suggestedValue);
            }
        }

        return $normalizedValues;
    }

    /**
     * @param string $attributeType
     * @param SuggestedValue $suggestedValue
     *
     * @return array
     */
    private function normalizeValue(string $attributeType, SuggestedValue $suggestedValue): array
    {
        $normalizedValue = null;

        switch ($attributeType) {
            case AttributeTypes::IDENTIFIER:
            case AttributeTypes::TEXT:
            case AttributeTypes::TEXTAREA:
                $normalizedValue = (new TextNormalizer())->normalize($suggestedValue);
                break;
            case AttributeTypes::NUMBER:
                $normalizedValue = (new NumberNormalizer($this->attributeRepository))
                    ->normalize($suggestedValue);
                break;
            case AttributeTypes::BOOLEAN:
                $normalizedValue = (new BooleanNormalizer())->normalize($suggestedValue);
                break;
            case AttributeTypes::OPTION_SIMPLE_SELECT:
                $normalizedValue = (new SimpleSelectNormalizer($this->attributeOptionRepository))
                    ->normalize($suggestedValue);
                break;
            case AttributeTypes::OPTION_MULTI_SELECT:
                $normalizedValue = (new MultiSelectNormalizer($this->attributeOptionRepository))
                    ->normalize($suggestedValue);
                break;
            case AttributeTypes::METRIC:
                $normalizedValue = (new MetricNormalizer(
                    $this->attributeRepository,
                    $this->measureConverter
                ))->normalize($suggestedValue);
                break;
            default:
                // Unsupported attribute type, do not normalize this data
                $normalizedValue = [];
        }

        return $normalizedValue;
    }
}
