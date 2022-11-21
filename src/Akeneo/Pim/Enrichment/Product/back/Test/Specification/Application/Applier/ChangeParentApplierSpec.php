<?php

namespace Specification\Akeneo\Pim\Enrichment\Product\Application\Applier;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ChangeParent;
use Akeneo\Pim\Enrichment\Product\Application\Applier\ChangeParentApplier;
use Akeneo\Pim\Enrichment\Product\Application\Applier\UserIntentApplier;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;

class ChangeParentApplierSpec extends ObjectBehavior
{
    function let(ObjectUpdaterInterface $updater)
    {
        $this->beConstructedWith($updater);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ChangeParentApplier::class);
        $this->shouldImplement(UserIntentApplier::class);
    }

    function it_applies_set_parent_user_intent(ObjectUpdaterInterface $updater): void
    {
        $product = new Product();
        $setParent = new ChangeParent('product_model_code');
        $updater->update($product,['parent' => 'product_model_code'])->shouldBeCalledOnce();

        $this->apply($setParent, $product, 1);
    }

    function it_throws_an_exception_when_user_intent_is_not_supported(): void
    {
        $product = new Product();
        $setEnabledUserIntent = new SetEnabled(true);
        $this->shouldThrow(\InvalidArgumentException::class)->during('apply', [$setEnabledUserIntent, $product, 1]);
    }

    function it_does_not_update_if_parent_is_already_set_on_the_product(
        ObjectUpdaterInterface $updater,
        ProductInterface $product,
        ProductModelInterface $productModel
    ): void
    {
        $setParent = new ChangeParent('product_model_code');
        $product->getParent()->willReturn($productModel);
        $productModel->getCode()->willReturn('product_model_code');
        $updater->update()->shouldNotBeCalled();

        $this->apply($setParent, $product, 1);
    }
}
