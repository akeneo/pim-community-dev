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

namespace Akeneo\Pim\Automation\FranklinInsights\Application\Normalizer\Standard\SuggestedValue;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\ValueObject\SuggestedValue;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Bundle\MeasureBundle\Convert\MeasureConverter;
use Akeneo\Tool\Bundle\MeasureBundle\Exception\MeasureException;

/**
 * Normalizes a suggested value to Akeneo standard format for a metric attribute type.
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
final class MetricNormalizer
{
    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var MeasureConverter */
    private $measureConverter;

    /**
     * @param AttributeRepositoryInterface $attributeRepository
     * @param MeasureConverter $measureConverter
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        MeasureConverter $measureConverter
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->measureConverter = $measureConverter;
    }

    /**
     * @param SuggestedValue $suggestedValue
     *
     * @return array
     */
    public function normalize(SuggestedValue $suggestedValue): array
    {
        preg_match(
            '/(?P<amount>[-+]?[0-9]*\.?[0-9]+)\s+(?P<unit>.+)/',
            $suggestedValue->value(),
            $matches
        );

        if (empty($matches) || empty($matches['amount'])) {
            return [];
        }

        $normalizedMetric = [
            'amount' => $matches['amount'],
            'unit' => $matches['unit'],
        ];

        $attribute = $this->attributeRepository->findOneByIdentifier($suggestedValue->pimAttributeCode());

        if (null === $attribute) {
            return [];
        }

        try {
            $normalizedMetric = $this->convertToDefaultAttributeMetricUnit($attribute, $normalizedMetric);
        } catch (MeasureException $exception) {
            return [];
        }

        return [
            $suggestedValue->pimAttributeCode() => [[
                'scope' => null,
                'locale' => null,
                'data' => $normalizedMetric,
            ]],
        ];
    }

    /**
     * @param AttributeInterface $attribute
     * @param array $normalizedMetric
     *
     * @throws MeasureException
     *
     * @return array
     */
    private function convertToDefaultAttributeMetricUnit(AttributeInterface $attribute, array $normalizedMetric): array
    {
        $this->measureConverter->setFamily($attribute->getMetricFamily());

        $convertedValue = $this->measureConverter->convert(
            $normalizedMetric['unit'],
            $attribute->getDefaultMetricUnit(),
            $normalizedMetric['amount']
        );

        return [
            'amount' => $attribute->isDecimalsAllowed() ? (float) $convertedValue : (int) $convertedValue,
            'unit' => $attribute->getDefaultMetricUnit(),
        ];
    }
}
