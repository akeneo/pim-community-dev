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

use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Category\GetCategoryTranslations;
use Akeneo\Tool\Component\Classification\CategoryAwareInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;

class CategoriesSelectorSpec extends ObjectBehavior
{
    public function it_returns_property_name_supported(
        GetCategoryTranslations $getCategoryTranslations
    ) {
        $this->beConstructedWith($getCategoryTranslations);

        $this->supports(['type' => 'code'], 'categories')->shouldReturn(true);
        $this->supports(['type' => 'code'], 'family')->shouldReturn(false);
        $this->supports(['type' => 'label'], 'categories')->shouldReturn(true);
        $this->supports(['type' => 'unknown'], 'categories')->shouldReturn(false);
    }

    public function it_selects_the_code(
        GetCategoryTranslations $getCategoryTranslations,
        CategoryAwareInterface $entity,
        CategoryInterface $category1,
        CategoryInterface $category2
    ) {
        $this->beConstructedWith($getCategoryTranslations);

        $category1->getCode()->willReturn('shoes');
        $category2->getCode()->willReturn('men');
        $entity->getCategories()->willReturn(new ArrayCollection([$category1->getWrappedObject(), $category2->getWrappedObject()]));

        $this->applySelection(['type' => 'code', 'separator' => ','], $entity)->shouldReturn('shoes,men');
    }

    public function it_selects_the_label(
        GetCategoryTranslations $getCategoryTranslations,
        CategoryAwareInterface $entity,
        CategoryInterface $category1,
        CategoryInterface $category2
    ) {
        $this->beConstructedWith($getCategoryTranslations);

        $category1->getCode()->willReturn('shoes');
        $category2->getCode()->willReturn('men');
        $entity->getCategories()->willReturn(new ArrayCollection([$category1->getWrappedObject(), $category2->getWrappedObject()]));
        $getCategoryTranslations->byCategoryCodesAndLocale(['shoes', 'men'], 'fr_FR')
            ->willReturn(['shoes' => 'chaussures']);

        $this->applySelection([
            'type' => 'label',
            'locale' => 'fr_FR',
            'separator' => ','
        ], $entity)->shouldReturn('chaussures,[men]');
    }
}
