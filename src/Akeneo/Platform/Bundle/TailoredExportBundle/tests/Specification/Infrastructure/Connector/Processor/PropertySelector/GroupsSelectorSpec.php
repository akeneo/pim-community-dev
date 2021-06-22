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

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Group\GetGroupTranslations;
use PhpSpec\ObjectBehavior;

class GroupsSelectorSpec extends ObjectBehavior
{
    public function let(GetGroupTranslations $getGroupTranslations)
    {
        $this->beConstructedWith($getGroupTranslations);
    }

    public function it_supports_groups_selection()
    {
        $this->supports(['type' => 'code'], 'groups')->shouldReturn(true);
        $this->supports(['type' => 'code'], 'categories')->shouldReturn(false);
        $this->supports(['type' => 'label'], 'groups')->shouldReturn(true);
        $this->supports(['type' => 'unknown'], 'groups')->shouldReturn(false);
    }

    public function it_selects_the_code(ProductInterface $product)
    {
        $product->getGroupCodes()->willReturn(['first_group', 'second_group', 'another_group']);

        $this->applySelection([
            'type' => 'code',
            'separator' => ','
        ], $product)->shouldReturn('first_group,second_group,another_group');
    }

    public function it_selects_the_label(
        GetGroupTranslations $getGroupTranslations,
        ProductInterface $product
    ) {
        $product->getGroupCodes()->willReturn(['first_group', 'second_group', 'another_group']);
        $getGroupTranslations->byGroupCodesAndLocale(['first_group', 'second_group', 'another_group'], 'fr_FR')
            ->willReturn(['first_group' => 'Premier groupe', 'second_group' => 'Second groupe']);

        $this->applySelection([
            'type' => 'label',
            'locale' => 'fr_FR',
            'separator' => ','
        ], $product)->shouldReturn('Premier groupe,Second groupe,[another_group]');
    }
}
