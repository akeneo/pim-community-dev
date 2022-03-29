<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\Application\Applier\Groups;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Groups\AddToGroups;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Groups\RemoveFromGroups;
use Akeneo\Pim\Enrichment\Product\Application\Applier\Groups\AddToGroupsApplier;
use Akeneo\Pim\Enrichment\Product\Application\Applier\Groups\RemoveFromGroupsApplier;
use Akeneo\Pim\Enrichment\Product\Application\Applier\UserIntentApplier;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;

class RemoveFromGroupsApplierSpec extends ObjectBehavior
{
    function let(
        ObjectUpdaterInterface $productUpdater
    ) {
        $this->beConstructedWith($productUpdater);
    }

    function it_is_an_user_intent_applier()
    {
        $this->shouldHaveType(RemoveFromGroupsApplier::class);
        $this->shouldImplement(UserIntentApplier::class);
    }

    function it_applies_a_remove_groups_user_intent_on_a_product(
        ObjectUpdaterInterface $productUpdater,
        Product $product
    ) {
        $userIntent = new RemoveFromGroups(['promotion']);
        $product->getGroupCodes()->willReturn(['promotion', 'foo', 'bar']);
        $productUpdater->update($product, ['groups' => ['foo', 'bar']])->shouldBeCalledOnce();

        $this->apply($userIntent, $product, 10);
    }

    function it_applies_a_remove_groups_user_intent_on_non_present_groups(
        ObjectUpdaterInterface $productUpdater,
        Product $product
    ) {
        $userIntent = new RemoveFromGroups(['toto']);
        $product->getGroupCodes()->willReturn(['promotion', 'foo', 'bar']);
        $productUpdater->update()->shouldNotBeCalled();

        $this->apply($userIntent, $product, 10);
    }
}
