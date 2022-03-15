<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\Infrastructure\Query;

use Akeneo\Pim\Enrichment\Category\API\Query\GetViewableCategories;
use Akeneo\Pim\Enrichment\Product\Domain\Model\ProductIdentifier;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetCategoryCodes;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetNonViewableCategoryCodes;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Query\SqlGetNonViewableCategoryCodes;
use PhpSpec\ObjectBehavior;

class SqlGetNonViewableCategoryCodesSpec extends ObjectBehavior
{
    function let(GetCategoryCodes $getCategoryCodes, GetViewableCategories $getViewableCategories)
    {
        $this->beConstructedWith($getCategoryCodes, $getViewableCategories);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SqlGetNonViewableCategoryCodes::class);
        $this->shouldImplement(GetNonViewableCategoryCodes::class);
    }

    function it_returns_non_viewable_category_codes_for_a_lit_of_product_identifiers(
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
