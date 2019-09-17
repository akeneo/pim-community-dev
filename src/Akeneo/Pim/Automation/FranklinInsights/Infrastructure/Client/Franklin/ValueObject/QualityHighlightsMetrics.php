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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\ValueObject;

class QualityHighlightsMetrics
{
    private $valueSuggested;
    private $nameAndValueSuggested;
    private $valueInError;
    private $valueValidated;

    public function __construct(array $rawMetrics)
    {
        $this->valueSuggested = $this->getFromRawMetrics('value_added', $rawMetrics);
        $this->nameAndValueSuggested = $this->getFromRawMetrics('added', $rawMetrics);
        $this->valueValidated = $this->getFromRawMetrics('value_validated', $rawMetrics);
        $this->valueInError = $this->getFromRawMetrics('value_mismatch', $rawMetrics);
    }

    public function getValueSuggested(): int
    {
        return $this->valueSuggested;
    }

    public function getNameAndValueSuggested(): int
    {
        return $this->nameAndValueSuggested;
    }

    public function getValueInError(): int
    {
        return $this->valueInError;
    }

    public function getValueValidated(): int
    {
        return $this->valueValidated;
    }

    private function getFromRawMetrics(string $metricCode, array $rawMetrics): int
    {
        if (!array_key_exists($metricCode, $rawMetrics)) {
            throw new \InvalidArgumentException(sprintf('Missing quality highlight metric "%s"', $metricCode));
        }

        return intval($rawMetrics[$metricCode]);
    }
}
