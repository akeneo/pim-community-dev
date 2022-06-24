<?php

namespace Specification\Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ValueUserIntent;
use Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\CategoriesUserIntentFactory;
use Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\UserIntentFactory;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;

class CategoriesUserIntentFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(CategoriesUserIntentFactory::class);
        $this->shouldImplement(UserIntentFactory::class);
    }

    function it_returns_category_user_intent() {
        $this->create('categories', ['categoryA', 'categoryA'])
            ->shouldBeLike([new SetCategories(['categoryA', 'categoryA'])]);
    }

    function it_returns_empty_set_categories_user_intent()
    {
        $this->create('categories', [])
            ->shouldBeLike([new SetCategories([])]);
    }

    function it_throws_an_exception_if_data_is_not_valid()
    {
        $this->shouldThrow(InvalidPropertyTypeException::class)
            ->during('create', ['categories', 'categoryA']);

        $this->shouldThrow(InvalidPropertyTypeException::class)
            ->during('create', ['categories', null]);
    }
}
