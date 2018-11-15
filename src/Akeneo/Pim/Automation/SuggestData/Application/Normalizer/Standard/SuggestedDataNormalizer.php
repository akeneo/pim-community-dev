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

namespace Akeneo\Pim\Automation\SuggestData\Application\Normalizer\Standard;

use Akeneo\Pim\Automation\SuggestData\Domain\Model\SuggestedData;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\SuggestedValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeOptionRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Bundle\MeasureBundle\Convert\MeasureConverter;
use Akeneo\Tool\Bundle\MeasureBundle\Exception\UnknownFamilyMeasureException;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class SuggestedDataNormalizer
{
    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var AttributeOptionRepositoryInterface */
    private $attributeOptionRepository;

    /** @var MeasureConverter */
    private $measureConverter;

    /**
     * @param AttributeRepositoryInterface $attributeRepository
     * @param AttributeOptionRepositoryInterface $attributeOptionRepository
     * @param MeasureConverter $measureConverter
     */
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
     * Returns suggested values in standard format.
     *
     * We first get the attribute types for each of the attributes of the suggested values.
     * The attribute types list is formatted as follow:
     *    [
     *        'attribute_code' => 'attribute_type',
     *    ]
     * If a suggested value refers to an attribute that does not exists, it will not be present in this list.
     *
     * @param SuggestedData $suggestedData
     *
     * @return array
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
            case AttributeTypes::NUMBER:
            case AttributeTypes::DATE:
                $normalizedValue = $this->handleSimpleValue($suggestedValue);
                break;
            case AttributeTypes::BOOLEAN:
                $normalizedValue = $this->handleBoolean($suggestedValue);
                break;
            case AttributeTypes::OPTION_SIMPLE_SELECT:
                $normalizedValue = $this->handleSimpleSelect($suggestedValue);
                break;
            case AttributeTypes::OPTION_MULTI_SELECT:
                $normalizedValue = $this->handleMultiSelect($suggestedValue);
                break;
            case AttributeTypes::METRIC:
                $normalizedValue = $this->handleMetric($suggestedValue);
                break;
            default:
                throw new \InvalidArgumentException(
                    sprintf('Unsupported attribute type "%s"', $attributeType)
                );
        }

        return $normalizedValue;
    }

    /**
     * @param SuggestedValue $suggestedValue
     *
     * @return array
     */
    private function handleSimpleValue(SuggestedValue $suggestedValue): array
    {
        return [
            $suggestedValue->pimAttributeCode() => [[
                'scope' => null,
                'locale' => null,
                'data' => '' === $suggestedValue->value() ? null : $suggestedValue->value(),
            ]],
        ];
    }

    /**
     * @param SuggestedValue $suggestedValue
     *
     * @return array
     */
    private function handleBoolean(SuggestedValue $suggestedValue): array
    {
        $data = null;
        $value = $suggestedValue->value();

        if (in_array($value, ['1', '0'])) {
            $data = (bool) $value;
        } elseif ('' !== $value) {
            $data = $value;
        }

        if (null === $data) {
            return [];
        }

        return [
            $suggestedValue->pimAttributeCode() => [[
                'scope' => null,
                'locale' => null,
                'data' => '' === $data ? null : $data,
            ]],
        ];
    }

    /**
     * @param SuggestedValue $suggestedValue
     *
     * @return array
     */
    private function handleSimpleSelect(SuggestedValue $suggestedValue): array
    {
        $data = $this->filterOptions($suggestedValue->pimAttributeCode(), $suggestedValue->value());
        if (null === $data) {
            return [];
        }

        return [
            $suggestedValue->pimAttributeCode() => [[
                'scope' => null,
                'locale' => null,
                'data' => '' === $data ? null : $data,
            ]],
        ];
    }

    /**
     * @param SuggestedValue $suggestedValue
     *
     * @return array
     */
    private function handleMultiSelect(SuggestedValue $suggestedValue): array
    {
        $value = $this->filterOptions($suggestedValue->pimAttributeCode(), $suggestedValue->value());
        if (null === $value) {
            return [];
        }

        $data = $value;
        if (!is_array($value)) {
            $data = array_filter(explode(',', $value));
            array_walk($data, 'trim');
        }

        if (null === $data) {
            return [];
        }

        return [
            $suggestedValue->pimAttributeCode() => [[
                'scope' => null,
                'locale' => null,
                'data' => '' === $data ? null : $data,
            ]],
        ];
    }

    /**
     * Filters attribute options that are not in the PIM.
     *
     * @param string $attributeCode
     * @param mixed $value
     *
     * @return null|string
     */
    private function filterOptions(string $attributeCode, $value): ?string
    {
        $codes = array_filter(explode(',', $value));
        $options = $this->attributeOptionRepository->findCodesByIdentifiers($attributeCode, $codes);

        if (empty($options)) {
            return null;
        }
        if (count($codes) === count($options)) {
            return $value;
        }

        return implode(',', array_map(function (AttributeOptionInterface $option) {
            return $option->getCode();
        }, $options));
    }

    /**
     * TODO: ensure the metric unit exists if the conversion step is removed.
     *
     * @param SuggestedValue $suggestedValue
     *
     * @return array
     */
    private function handleMetric(SuggestedValue $suggestedValue): array
    {
        preg_match("~^(?'value'[0-9.])[[:space:]](?'unit'[a-zA-Z_]+)$~", $suggestedValue->value(), $matches);

        if (empty($matches['value'] || empty($matches['unit']))) {
            throw new \InvalidArgumentException(sprintf('Invalid metric value: %s', $suggestedValue->value()));
        }

        $normalizedMetric = [
            'amount' => $matches['value'],
            'unit' => $matches['unit'],
        ];
        $normalizedMetric = $this->convertMetric($suggestedValue->pimAttributeCode(), $normalizedMetric);

        return [
            $suggestedValue->pimAttributeCode() => [[
                'scope' => null,
                'locale' => null,
                'data' => $normalizedMetric,
            ]],
        ];
    }

    /**
     * @param string $attributeCode
     * @param array $normalizedMetric
     *
     * @return array
     */
    private function convertMetric(string $attributeCode, array $normalizedMetric): array
    {
        $attribute = $this->getAttribute($attributeCode);
        try {
            $this->measureConverter->setFamily($attribute->getMetricFamily());
        } catch (UnknownFamilyMeasureException $exception) {
            return [];
        }

        $convertedValue = $this->measureConverter->convert(
            $normalizedMetric['unit'],
            $attribute->getDefaultMetricUnit(),
            $normalizedMetric['amount']
        );

        return [
            'amount' => $convertedValue,
            'unit' => $attribute->getDefaultMetricUnit(),
        ];
    }

    /**
     * @param string $attributeCode
     *
     * @return AttributeInterface
     */
    private function getAttribute(string $attributeCode): AttributeInterface
    {
        $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);

        if (null === $attribute) {
            throw new \InvalidArgumentException(sprintf(
                'Cannot find the attribute "%s" to get its metric standard unit',
                $attributeCode
            ));
        }

        return $attribute;
    }
}
