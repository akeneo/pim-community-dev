<?php

namespace Specification\Akeneo\Pim\Enrichment\Product\Application\Applier;

use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\RemoveParentInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\RemoveParent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\Application\Applier\RemoveParentApplier;
use Akeneo\Pim\Enrichment\Product\Application\Applier\UserIntentApplier;
use PhpSpec\ObjectBehavior;

class RemoveParentApplierSpec extends ObjectBehavior
{
    function let(RemoveParentInterface $removeParent)
    {
        $this->beConstructedWith($removeParent);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RemoveParentApplier::class);
        $this->shouldImplement(UserIntentApplier::class);
    }

    function it_applies_remove_parent_user_intent(
        RemoveParentInterface $removeParent,
        ProductInterface $product,
        ProductModelInterface $productModel
    ): void
    {
        $removeParentIntent = new RemoveParent();
        $product->getParent()->willReturn($productModel);
        $removeParent->from($product)->shouldBeCalledOnce();

        $this->apply($removeParentIntent, $product, 1);
    }

    function it_throws_an_exception_when_product_has_no_parent(ProductInterface $product): void
    {
        $removeParentIntent = new RemoveParent();
        $product->getParent()->willReturn(null);
        $this->shouldThrow(\InvalidArgumentException::class)->during('apply', [$removeParentIntent, $product, 1]);
    }

    function it_throws_an_exception_when_user_intent_is_not_supported(): void
    {
        $product = new Product();
        $setEnabledUserIntent = new SetEnabled(true);
        $this->shouldThrow(\InvalidArgumentException::class)->during('apply', [$setEnabledUserIntent, $product, 1]);
    }
}
