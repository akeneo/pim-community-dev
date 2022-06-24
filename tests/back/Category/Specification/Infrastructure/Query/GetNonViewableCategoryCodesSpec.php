<?php

declare(strict_types=1);

namespace Specification\Akeneo\Category\Infrastructure\Query;

use Akeneo\Category\Domain\Query\GetCategoryCodes;
use Akeneo\Category\Domain\Query\GetNonViewableCategoryCodes as GetNonViewableCategoryCodesInterface;
use Akeneo\Category\Domain\Query\GetViewableCategories;
use Akeneo\Category\Infrastructure\Query\GetNonViewableCategoryCodes;
use Akeneo\Pim\Enrichment\Product\Domain\Model\ProductIdentifier;
use PhpSpec\ObjectBehavior;

class GetNonViewableCategoryCodesSpec extends ObjectBehavior
{
    function let(GetCategoryCodes $getCategoryCodes, GetViewableCategories $getViewableCategories)
    {
        $this->beConstructedWith($getCategoryCodes, $getViewableCategories);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(GetNonViewableCategoryCodes::class);
        $this->shouldImplement(GetNonViewableCategoryCodesInterface::class);
    }

    function it_returns_non_viewable_category_codes_for_a_list_of_product_identifiers(
        GetCategoryCodes $getCategoryCodes,
        GetViewableCategories $getViewableCategories
    ) {
        $productIdentifier1 = ProductIdentifier::fromString('id1');
        $productIdentifier2 = ProductIdentifier::fromString('id2');
        $productIdentifier3 = ProductIdentifier::fromString('id3');

        $getCategoryCodes->fromProductIdentifiers([$productIdentifier1, $productIdentifier2, $productIdentifier3])
            ->willReturn([
                'id1' => ['categoryA', 'categoryB', 'categoryC'],
                'id2' => ['categoryA', 'categoryD', 'categoryE'],
            ]);
        $getViewableCategories->forUserId(['categoryA', 'categoryB', 'categoryC', 'categoryD', 'categoryE'], 10)
            ->willReturn(['categoryA', 'categoryB', 'categoryC', 'categoryD']);

        $this->fromProductIdentifiers([$productIdentifier1, $productIdentifier2, $productIdentifier3], 10)
            ->shouldreturn([
                'id1' => [],
                'id2' => ['categoryE'],
            ]);
    }
}
