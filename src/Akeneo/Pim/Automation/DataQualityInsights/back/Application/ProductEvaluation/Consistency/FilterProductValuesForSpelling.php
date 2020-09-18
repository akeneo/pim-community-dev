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

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Consistency;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ProductValuesCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetAllActivatedLocalesQueryInterface;

class FilterProductValuesForSpelling
{
    /** @var GetAllActivatedLocalesQueryInterface */
    private $allActivatedLocalesQuery;

    public function __construct(GetAllActivatedLocalesQueryInterface $allActivatedLocalesQuery)
    {
        $this->allActivatedLocalesQuery = $allActivatedLocalesQuery;
    }

    public function getFilteredProductValues(ProductValuesCollection $productValues): array
    {
        $isCatalogWithSingleLocale = $this->isCatalogWithSingleLocale();

        $textValues = $isCatalogWithSingleLocale ? $productValues->getTextValues() : $productValues->getLocalizableTextValues();
        $textareaValues = $isCatalogWithSingleLocale ? $productValues->getTextareaValues() : $productValues->getLocalizableTextareaValues();

        return array_merge(iterator_to_array($textValues), iterator_to_array($textareaValues));
    }

    private function isCatalogWithSingleLocale(): bool
    {
        return 1 === $this->allActivatedLocalesQuery->execute()->count();
    }
}
