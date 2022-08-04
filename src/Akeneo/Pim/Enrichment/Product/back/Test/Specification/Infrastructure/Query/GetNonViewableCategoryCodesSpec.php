<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\Infrastructure\Query;

use Akeneo\Pim\Enrichment\Category\API\Query\GetViewableCategories;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetCategoryCodesByProductUuidsInterface;
use Akeneo\Pim\Enrichment\Product\Domain\Model\ProductIdentifier;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetCategoryCodes;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetNonViewableCategoryCodes as GetNonViewableCategoryCodesInterface;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Query\GetNonViewableCategoryCodes;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;

class GetNonViewableCategoryCodesSpec extends ObjectBehavior
{
    function let(GetCategoryCodes $getCategoryCodes, GetViewableCategories $getViewableCategories) {
        $this->beConstructedWith($getCategoryCodes, $getViewableCategories);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(GetNonViewableCategoryCodes::class);
        $this->shouldImplement(GetNonViewableCategoryCodesInterface::class);
    }

    function it_returns_non_viewable_category_codes_for_a_list_of_product_uuids(
        GetCategoryCodes $getCategoryCodes,
        GetViewableCategories $getViewableCategories,
    ) {
        $uuid1 = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();
        $uuid3 = Uuid::uuid4();

        $getCategoryCodes->fromProductUuids([$uuid1, $uuid2, $uuid3])
            ->willReturn([
                $uuid1->toString() => ['categoryA', 'categoryB', 'categoryC'],
                $uuid2->toString() => ['categoryA', 'categoryD', 'categoryE'],
            ]);
        $getViewableCategories->forUserId(['categoryA', 'categoryB', 'categoryC', 'categoryD', 'categoryE'], 10)
            ->willReturn(['categoryA', 'categoryB', 'categoryC', 'categoryD']);
        $this->fromProductUuids([$uuid1, $uuid2, $uuid3], 10)
            ->shouldReturn([
                $uuid1->toString() => [],
                $uuid2->toString() => ['categoryE']
            ]);
    }
}
