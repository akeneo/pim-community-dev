<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationResultStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CriterionEvaluationResultStatusCollection implements \IteratorAggregate
{
    /** @var ChannelLocaleDataCollection */
    private $resultsStatus;

    public function __construct()
    {
        $this->resultsStatus = new ChannelLocaleDataCollection();
    }

    public function add(ChannelCode $channelCode, LocaleCode $localeCode, CriterionEvaluationResultStatus $resultStatus): self
    {
        $this->resultsStatus->addToChannelAndLocale($channelCode, $localeCode, $resultStatus);

        return $this;
    }

    public function get(ChannelCode $channelCode, LocaleCode $localeCode): ?CriterionEvaluationResultStatus
    {
        return $this->resultsStatus->getByChannelAndLocale($channelCode, $localeCode);
    }

    public function toArrayString(): array
    {
        return $this->resultsStatus->mapWith(function (CriterionEvaluationResultStatus $resultStatus) {
            return strval($resultStatus);
        });
    }

    public function getIterator(): \Iterator
    {
        return $this->resultsStatus->getIterator();
    }

    public static function fromArrayString(array $rawStatusCollection): self
    {
        $statusCollection = new self();
        $statusCollection->resultsStatus = ChannelLocaleDataCollection::fromNormalizedChannelLocaleData($rawStatusCollection, function ($status) {
            return new CriterionEvaluationResultStatus($status);
        });

        return $statusCollection;
    }
}
