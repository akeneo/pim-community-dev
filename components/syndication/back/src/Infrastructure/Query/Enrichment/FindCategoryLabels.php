<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Syndication\Infrastructure\Query\Enrichment;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Category\GetCategoryTranslations;
use Akeneo\Platform\Syndication\Domain\Query\FindCategoryLabelsInterface;

class FindCategoryLabels implements FindCategoryLabelsInterface
{
    private GetCategoryTranslations $getCategoryTranslations;

    public function __construct(GetCategoryTranslations $getCategoryTranslations)
    {
        $this->getCategoryTranslations = $getCategoryTranslations;
    }

    public function byCodes(array $categoryCodes, string $locale): array
    {
        return $this->getCategoryTranslations
            ->byCategoryCodesAndLocale($categoryCodes, $locale);
    }
}
