<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\Application\Applier\Groups;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Groups\AddGroups;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Groups\RemoveGroups;
use Akeneo\Pim\Enrichment\Product\Application\Applier\Groups\AddGroupsApplier;
use Akeneo\Pim\Enrichment\Product\Application\Applier\Groups\RemoveGroupsApplier;
use Akeneo\Pim\Enrichment\Product\Application\Applier\UserIntentApplier;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;

class RemoveGroupsApplierSpec extends ObjectBehavior
{
    function let(
        ObjectUpdaterInterface $productUpdater
    ) {
        $this->beConstructedWith($productUpdater);
    }

    function it_is_an_user_intent_applier()
    {
        $this->shouldHaveType(RemoveGroupsApplier::class);
        $this->shouldImplement(UserIntentApplier::class);
    }

    function it_applies_a_remove_groups_user_intent_on_a_product(
        ObjectUpdaterInterface $productUpdater,
        Product $product
    ) {
        $userIntent = new RemoveGroups(['promotion']);
        $product->getGroupCodes()->willReturn(['promotion', 'foo', 'bar']);
        $productUpdater->update($product, ['groups' => ['foo', 'bar']])->shouldBeCalledOnce();

        $this->apply($userIntent, $product, 10);
    }

    function it_applies_a_remove_groups_user_intent_on_non_present_groups(
        ObjectUpdaterInterface $productUpdater,
        Product $product
    ) {
        $userIntent = new RemoveGroups(['toto']);
        $product->getGroupCodes()->willReturn(['promotion', 'foo', 'bar']);
        $productUpdater->update()->shouldNotBeCalled();

        $this->apply($userIntent, $product, 10);
    }
}
