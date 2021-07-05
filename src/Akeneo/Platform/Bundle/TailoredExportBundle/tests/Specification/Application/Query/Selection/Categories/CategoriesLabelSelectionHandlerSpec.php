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

namespace Specification\Akeneo\Platform\TailoredExport\Application\Query\Selection\Categories;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Category\GetCategoryTranslations;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\Boolean\BooleanSelection;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\Categories\CategoriesLabelSelection;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\BooleanValue;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\CategoriesValue;
use PhpSpec\ObjectBehavior;

class CategoriesLabelSelectionHandlerSpec extends ObjectBehavior
{
    public function let(GetCategoryTranslations $getCategoryTranslations)
    {
        $this->beConstructedWith($getCategoryTranslations);
    }

    public function it_applies_the_selection(GetCategoryTranslations $getCategoryTranslations)
    {
        $selection = new CategoriesLabelSelection('-', 'fr_FR');
        $value = new CategoriesValue(['category_code1', 'category_code2', 'category_code3']);

        $getCategoryTranslations->byCategoryCodesAndLocale(
            ['category_code1', 'category_code2', 'category_code3'],
            'fr_FR'
        )->willReturn([
            'category_code1' => 'Catégorie 1',
            'category_code2' => 'Catégorie 2',
            'category_code3' => 'Catégorie 3',
        ]);

        $this->applySelection($selection, $value)
            ->shouldReturn('Catégorie 1-Catégorie 2-Catégorie 3');
    }

    public function it_does_not_apply_selection_on_not_supported_selections_and_values()
    {
        $notSupportedSelection = new BooleanSelection();
        $notSupportedValue = new BooleanValue(true);

        $this
            ->shouldThrow(new \InvalidArgumentException('Cannot apply Categories selection on this entity'))
            ->during('applySelection', [$notSupportedSelection, $notSupportedValue]);
    }

    public function it_supports_categories_label_selection_with_categories_value()
    {
        $selection = new CategoriesLabelSelection('-', 'fr_FR');
        $value = new CategoriesValue([]);

        $this->supports($selection, $value)->shouldReturn(true);
    }

    public function it_does_not_support_other_selections_and_values()
    {
        $notSupportedSelection = new BooleanSelection();
        $notSupportedValue = new BooleanValue(true);

        $this->supports($notSupportedSelection, $notSupportedValue)->shouldReturn(false);
    }
}
