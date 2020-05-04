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

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ProductValues;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ProductValuesCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetAllActivatedLocalesQueryInterface;

class FilterProductValuesForTitleFormatting
{
    /** @var GetAllActivatedLocalesQueryInterface */
    private $allActivatedLocalesQuery;

    public function __construct(GetAllActivatedLocalesQueryInterface $allActivatedLocalesQuery)
    {
        $this->allActivatedLocalesQuery = $allActivatedLocalesQuery;
    }

    public function getMainTitleValues(ProductValuesCollection $productValues): ?ProductValues
    {
        if ($this->isCatalogWithSingleLocale()) {
            foreach ($productValues->getTextValues() as $textValues) {
                if ($textValues->getAttribute()->isMainTitle()) {
                    return $textValues;
                }
            }

            return null;
        }

        return $productValues->getLocalizableMainTitleValues();
    }

    private function isCatalogWithSingleLocale(): bool
    {
        return 1 === $this->allActivatedLocalesQuery->execute()->count();
    }
}
