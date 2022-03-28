<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\Application\Applier\Groups;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Groups\SetGroups;
use Akeneo\Pim\Enrichment\Product\Application\Applier\Groups\SetGroupsApplier;
use Akeneo\Pim\Enrichment\Product\Application\Applier\UserIntentApplier;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;

class SetGroupsApplierSpec extends ObjectBehavior
{
    function let(
        ObjectUpdaterInterface $productUpdater
    ) {
        $this->beConstructedWith($productUpdater);
    }

    function it_is_an_user_intent_applier()
    {
        $this->shouldHaveType(SetGroupsApplier::class);
        $this->shouldImplement(UserIntentApplier::class);
    }

    function it_applies_a_set_groups_user_intent(
        ObjectUpdaterInterface $productUpdater,
        ProductInterface $product
    ) {
        $userIntent = new SetGroups(['promotion', 'toto']);
        $product->getGroupCodes()->willReturn([]);

        $productUpdater->update($product, ['groups' => ['promotion', 'toto']])->shouldBeCalledOnce();

        $this->apply($userIntent, $product, 10);
    }

    function it_does_not_update_if_groups_are_the_same(
        ObjectUpdaterInterface $productUpdater,
        ProductInterface $product
    ) {
        $userIntent = new SetGroups(['promotion', 'toto']);
        $product->getGroupCodes()->willReturn(['toto', 'promotion']);

        $productUpdater->update()->shouldNotBeCalled();

        $this->apply($userIntent, $product, 10);
    }
}
