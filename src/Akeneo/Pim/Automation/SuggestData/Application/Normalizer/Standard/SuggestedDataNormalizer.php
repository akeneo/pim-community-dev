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
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeOptionRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Bundle\MeasureBundle\Convert\MeasureConverter;

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
     * @param SuggestedData $suggestedData
     *
     * @return array
     */
    public function normalize(SuggestedData $suggestedData): array
    {
        $normalized = [];
        $suggestedValues = $suggestedData->getValues();
        $attributeTypes = $this->attributeRepository->getAttributeTypeByCodes(array_keys($suggestedValues));

        foreach ($suggestedValues as $attributeCode => $value) {
            if (!isset($attributeTypes[$attributeCode])) {
                continue;
            }
            $attributeType = $attributeTypes[$attributeCode];
            if (AttributeTypes::OPTION_SIMPLE_SELECT === $attributeType ||
                AttributeTypes::OPTION_MULTI_SELECT === $attributeType
            ) {
                $value = $this->filterOptions($attributeCode, $value);
                if (null === $value) {
                    continue;
                }
            }

            $normalized[$attributeCode] = $this->normalizeValue($attributeType, $attributeCode, $value);
        }

        return $normalized;
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
     * @param string $attributeType
     * @param string $attributeCode
     * @param mixed $value
     *
     * @return array
     */
    private function normalizeValue(string $attributeType, string $attributeCode, $value): array
    {
        $data = null;

        switch ($attributeType) {
            case AttributeTypes::IDENTIFIER:
            case AttributeTypes::TEXT:
            case AttributeTypes::TEXTAREA:
            case AttributeTypes::NUMBER:
            case AttributeTypes::OPTION_SIMPLE_SELECT:
            case AttributeTypes::DATE:
                $data = $value;
                break;
            case AttributeTypes::BOOLEAN:
                $data = $this->handleBoolean($value);
                break;
            case AttributeTypes::OPTION_MULTI_SELECT:
                $data = $this->handleMultiSelect($value);
                break;
            case AttributeTypes::METRIC:
                $data = $this->handleMetric($value);
                $data = $this->convertMetric($data, $attributeCode);
                break;
            default:
                throw new \InvalidArgumentException(
                    sprintf('Unsupported attribute type "%s"', $attributeType)
                );
        }

        return [
            [
                'scope' => null,
                'locale' => null,
                'data' => '' === $data ? null : $data,
            ],
        ];
    }

    /**
     * @param string $value
     *
     * @return bool|null
     */
    private function handleBoolean(string $value): ?bool
    {
        $data = null;

        if (in_array($value, ['1', '0'])) {
            $data = (bool) $value;
        } elseif ('' !== $value) {
            $data = $value;
        }

        return $data;
    }

    /**
     * @param string $value
     *
     * @return array
     */
    private function handleMultiSelect(string $value): array
    {
        $data = $value;

        if (!is_array($value)) {
            $data = array_filter(explode(',', $value));
            array_walk($data, 'trim');
        }

        return $data;
    }

    /**
     * TODO: ensure the metric unit exists if the conversion step is removed.
     *
     * @param string $value
     *
     * @return array
     */
    private function handleMetric(string $value): array
    {
        preg_match("~^(?'value'[0-9.])[[:space:]](?'unit'[a-zA-Z_]+)$~", $value, $matches);

        if (empty($matches['value'] || empty($matches['unit']))) {
            throw new \InvalidArgumentException(sprintf('Invalid metric value: %s', $value));
        }

        return [
            'amount' => $matches['value'],
            'unit' => $matches['unit'],
        ];
    }

    /**
     * @param array $standardFormat
     * @param string $attributeCode
     *
     * @return array
     */
    private function convertMetric(array $standardFormat, string $attributeCode): array
    {
        $attribute = $this->getAttribute($attributeCode);
        $this->measureConverter->setFamily($attribute->getMetricFamily());

        $convertedValue = $this->measureConverter->convert(
            $standardFormat['unit'],
            $attribute->getDefaultMetricUnit(),
            $standardFormat['amount']
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
