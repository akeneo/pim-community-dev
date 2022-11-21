<?php

namespace Specification\Akeneo\Pim\Enrichment\Product\Application\Applier;

use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\RemoveParentInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ConvertToSimpleProduct;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\Application\Applier\ConvertToSimpleProductApplier;
use Akeneo\Pim\Enrichment\Product\Application\Applier\UserIntentApplier;
use PhpSpec\ObjectBehavior;

class ConvertToSimpleProductApplierSpec extends ObjectBehavior
{
    function let(RemoveParentInterface $removeParent)
    {
        $this->beConstructedWith($removeParent);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ConvertToSimpleProductApplier::class);
        $this->shouldImplement(UserIntentApplier::class);
    }

    function it_applies_remove_parent_user_intent(
        RemoveParentInterface $removeParent,
        ProductInterface $product
    ): void
    {
        $removeParentIntent = new ConvertToSimpleProduct();
        $product->isVariant()->willReturn(true);
        $removeParent->from($product)->shouldBeCalledOnce();

        $this->apply($removeParentIntent, $product, 1);
    }

    function it_does_nothing_when_product_has_no_parent(
        ProductInterface $product,
        RemoveParentInterface $removeParent
    ): void
    {
        $removeParentIntent = new ConvertToSimpleProduct();
        $product->isVariant()->willReturn(false);

        $removeParent->from($product)->shouldNotBeCalled();

        $this->apply($removeParentIntent, $product, 1);
    }

    function it_throws_an_exception_when_user_intent_is_not_supported(): void
    {
        $product = new Product();
        $setEnabledUserIntent = new SetEnabled(true);
        $this->shouldThrow(\InvalidArgumentException::class)->during('apply', [$setEnabledUserIntent, $product, 1]);
    }
}
