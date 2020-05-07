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

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleDataCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\CriterionEvaluationResultStatusCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\CriterionRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationResultStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;

final class CriterionEvaluationResult
{
    /** @var CriterionRateCollection */
    private $rates;

    /** @var CriterionEvaluationResultStatusCollection */
    private $statusCollection;

    /** @var ChannelLocaleDataCollection */
    private $improvableAttributes;

    /** @var ChannelLocaleDataCollection[] */
    private $data;

    public function __construct()
    {
        $this->rates = new CriterionRateCollection();
        $this->statusCollection = new CriterionEvaluationResultStatusCollection();
        $this->improvableAttributes = new ChannelLocaleDataCollection();
        $this->data = [];
    }

    public function getRates(): CriterionRateCollection
    {
        return $this->rates;
    }

    public function getDataToArray(): array
    {
        return array_map(function (ChannelLocaleDataCollection $data) {
            return $data->toArray();
        }, $this->data);
    }

    public function getStatus(): CriterionEvaluationResultStatusCollection
    {
        return $this->statusCollection;
    }

    public function addRate(ChannelCode $channelCode, LocaleCode $localeCode, Rate $rate): self
    {
        $this->rates->addRate($channelCode, $localeCode, $rate);

        return $this;
    }

    public function addStatus(ChannelCode $channelCode, LocaleCode $localeCode, CriterionEvaluationResultStatus $status): self
    {
        $this->statusCollection->add($channelCode, $localeCode, $status);

        return $this;
    }

    public function addRateByAttributes(ChannelCode $channelCode, LocaleCode $localeCode, array $rateByAttributes): self
    {
        $this->addData('attributes_with_rates', $channelCode, $localeCode, $rateByAttributes);

        return $this;
    }

    public function addData(string $name, ChannelCode $channelCode, LocaleCode $localeCode, $data): self
    {
        if (!isset($this->data[$name])) {
            $this->data[$name] = new ChannelLocaleDataCollection();
        }

        $this->data[$name]->addToChannelAndLocale($channelCode, $localeCode, $data);

        return $this;
    }
}
