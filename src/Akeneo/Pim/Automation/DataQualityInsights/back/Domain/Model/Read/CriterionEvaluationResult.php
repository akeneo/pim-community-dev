<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleDataCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\CriterionEvaluationResultStatusCollection;

final class CriterionEvaluationResult
{
    /** @var ChannelLocaleRateCollection */
    private $rates;

    /** @var array */
    private $data;

    /** @var CriterionEvaluationResultStatusCollection */
    private $statusCollection;

    public function __construct(ChannelLocaleRateCollection $rates, CriterionEvaluationResultStatusCollection $statusCollection, array $data)
    {
        $this->rates = $rates;
        $this->data = $data;
        $this->statusCollection = $statusCollection;
    }

    public function getRates(): ChannelLocaleRateCollection
    {
        return $this->rates;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getAttributes(): ChannelLocaleDataCollection
    {
        $attributes = $this->data['attributes_with_rates'] ?? [];

        if (count($attributes) > 0) {
            return ChannelLocaleDataCollection::fromNormalizedChannelLocaleData($attributes, function ($attributes) {
                if (!is_array($attributes)) {
                    return [];
                }

                return array_keys(array_filter($attributes, fn($rate) => $rate < 100));
            });
        }

        // The 'attributes' array key is deprecated but kept here to allow backward compatibility
        $attributes = $this->data['attributes'] ?? [];

        return ChannelLocaleDataCollection::fromNormalizedChannelLocaleData($attributes, fn($attributeCodes) => is_array($attributeCodes) ? $attributeCodes : []);
    }

    public function getStatus(): CriterionEvaluationResultStatusCollection
    {
        return $this->statusCollection;
    }
}
