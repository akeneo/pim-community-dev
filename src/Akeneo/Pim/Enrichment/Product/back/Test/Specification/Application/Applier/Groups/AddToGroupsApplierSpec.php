<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\Application\Applier\Groups;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Groups\AddToGroups;
use Akeneo\Pim\Enrichment\Product\Application\Applier\Groups\AddToGroupsApplier;
use Akeneo\Pim\Enrichment\Product\Application\Applier\UserIntentApplier;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;

class AddToGroupsApplierSpec extends ObjectBehavior
{
    function let(
        ObjectUpdaterInterface $productUpdater
    ) {
        $this->beConstructedWith($productUpdater);
    }

    function it_is_an_user_intent_applier()
    {
        $this->shouldHaveType(AddToGroupsApplier::class);
        $this->shouldImplement(UserIntentApplier::class);
    }

    function it_applies_a_add_groups_user_intent_on_a_new_product(
        ObjectUpdaterInterface $productUpdater,
        Product $product
    ) {
        $userIntent = new AddToGroups(['promotion', 'toto']);
        $product->getGroupCodes()->willReturn([]);
        $productUpdater->update($product, ['groups' => ['promotion', 'toto']])->shouldBeCalledOnce();

        $this->apply($userIntent, $product, 10);
    }

    function it_applies_a_add_groups_user_intent_on_an_existing_product_with_groups(
        ObjectUpdaterInterface $productUpdater,
        Product $product
    ) {
        $userIntent = new AddToGroups(['toto']);
        $product->getGroupCodes()->willReturn(['promotion']);
        $productUpdater->update($product, ['groups' => ['promotion', 'toto']])->shouldBeCalledOnce();

        $this->apply($userIntent, $product, 10);
    }

    function it_ignores_duplicate_groups(
        ObjectUpdaterInterface $productUpdater,
        Product $product
    ) {
        $userIntent = new AddToGroups(['promotion']);
        $product->getGroupCodes()->willReturn(['promotion']);
        $productUpdater->update()->shouldNotBeCalled();

        $this->apply($userIntent, $product, 10);
    }
}
