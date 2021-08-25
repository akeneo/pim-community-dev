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

namespace Specification\Akeneo\Platform\TailoredExport\Infrastructure\Query\Enrichment;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Category\GetCategoryTranslations;
use Akeneo\Platform\TailoredExport\Infrastructure\Query\Enrichment\FindCategoryLabels;
use PhpSpec\ObjectBehavior;

class FindCategoryLabelsSpec extends ObjectBehavior
{
    public function let(
        GetCategoryTranslations $getCategoryTranslations
    ): void {
        $this->beConstructedWith($getCategoryTranslations);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(FindCategoryLabels::class);
    }

    public function it_finds_the_label_of_multiple_categories(
        GetCategoryTranslations $getCategoryTranslations
    ): void {
        $categoryCodes = ['winter', 'summer'];
        $localeCode = 'fr_FR';

        $expectedLabel = ['winter' => 'Hiver', 'summer' => 'Été', 'automn' => null];
        $getCategoryTranslations->byCategoryCodesAndLocale($categoryCodes, $localeCode)
            ->willReturn($expectedLabel);

        $this->byCodes($categoryCodes, $localeCode)->shouldReturn($expectedLabel);
    }
}
