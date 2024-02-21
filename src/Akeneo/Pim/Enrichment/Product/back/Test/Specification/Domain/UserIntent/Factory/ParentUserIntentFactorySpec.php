<?php

namespace Specification\Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ChangeParent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ConvertToSimpleProduct;
use Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\ParentUserIntentFactory;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;

class ParentUserIntentFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ParentUserIntentFactory::class);
    }

    function it_returns_change_parent()
    {
        $this->create('parent', 'new_parent')->shouldBeLike([new ChangeParent('new_parent')]);
    }

    function it_returns_convert_to_simple_product()
    {
        $this->create('parent', null)->shouldBeLike([new ConvertToSimpleProduct()]);
        $this->create('parent', '')->shouldBeLike([new ConvertToSimpleProduct()]);
    }

    function it_throws_an_exception_if_data_is_not_valid()
    {
        $this->shouldThrow(InvalidPropertyTypeException::class)
            ->during('create', ['parent', 12]);
    }
}
