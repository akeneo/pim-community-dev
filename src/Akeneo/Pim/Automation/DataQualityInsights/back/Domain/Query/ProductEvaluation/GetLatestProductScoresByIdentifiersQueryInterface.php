<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetLatestProductScoresByIdentifiersQueryInterface
{
    /**
     * @param string[] $productIdentifiers
     *
     * @return ChannelLocaleRateCollection[]
     */
    public function byProductIdentifiers(array $productIdentifiers): array;
}
