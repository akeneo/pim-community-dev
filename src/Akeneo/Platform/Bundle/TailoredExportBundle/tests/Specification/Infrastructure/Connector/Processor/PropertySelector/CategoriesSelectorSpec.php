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

namespace Specification\Akeneo\Platform\TailoredExport\Infrastructure\Connector\Processor\PropertySelector;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Category\GetCategoryTranslations;
use Akeneo\Tool\Component\Classification\CategoryAwareInterface;
use PhpSpec\ObjectBehavior;

class CategoriesSelectorSpec extends ObjectBehavior
{
    public function let(GetCategoryTranslations $getCategoryTranslations)
    {
        $this->beConstructedWith($getCategoryTranslations);
    }

    public function it_supports_categories_selection()
    {
        $this->supports(['type' => 'code'], 'categories')->shouldReturn(true);
        $this->supports(['type' => 'code'], 'family')->shouldReturn(false);
        $this->supports(['type' => 'label'], 'categories')->shouldReturn(true);
        $this->supports(['type' => 'unknown'], 'categories')->shouldReturn(false);
    }

    public function it_selects_the_code(
        GetCategoryTranslations $getCategoryTranslations,
        CategoryAwareInterface $entity
    ) {
        $entity->getCategoryCodes()->willReturn(['shoes', 'men']);

        $this->applySelection(['type' => 'code', 'separator' => ','], $entity)->shouldReturn('shoes,men');
    }

    public function it_selects_the_label(
        GetCategoryTranslations $getCategoryTranslations,
        CategoryAwareInterface $entity
    ) {
        $entity->getCategoryCodes()->willReturn(['shoes', 'men']);
        $getCategoryTranslations->byCategoryCodesAndLocale(['shoes', 'men'], 'fr_FR')
            ->willReturn(['shoes' => 'chaussures']);

        $this->applySelection([
            'type' => 'label',
            'locale' => 'fr_FR',
            'separator' => ','
        ], $entity)->shouldReturn('chaussures,[men]');
    }
}
