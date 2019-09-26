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
    private $valuesSuggested;
    private $namesAndValuesSuggested;
    private $valuesInError;
    private $valuesValidated;

    public function __construct(array $rawMetrics)
    {
        $this->valuesSuggested = $this->getFromRawMetrics('value_added', $rawMetrics);
        $this->namesAndValuesSuggested = $this->getFromRawMetrics('added', $rawMetrics);
        $this->valuesValidated = $this->getFromRawMetrics('value_validated', $rawMetrics);
        $this->valuesInError = $this->getFromRawMetrics('value_mismatch', $rawMetrics);
    }

    public function getValuesSuggested(): int
    {
        return $this->valuesSuggested;
    }

    public function getNamesAndValuesSuggested(): int
    {
        return $this->namesAndValuesSuggested;
    }

    public function getValuesInError(): int
    {
        return $this->valuesInError;
    }

    public function getValuesValidated(): int
    {
        return $this->valuesValidated;
    }

    private function getFromRawMetrics(string $metricCode, array $rawMetrics): int
    {
        if (!array_key_exists($metricCode, $rawMetrics)) {
            throw new \InvalidArgumentException(sprintf('Missing quality highlight metric "%s"', $metricCode));
        }

        return intval($rawMetrics[$metricCode]);
    }
}
