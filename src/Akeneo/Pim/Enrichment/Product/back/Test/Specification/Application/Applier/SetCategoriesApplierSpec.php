<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\Application\Applier;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\Application\Applier\SetCategoriesApplier;
use Akeneo\Pim\Enrichment\Product\Application\Applier\UserIntentApplier;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetNonViewableCategoryCodes;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;

class SetCategoriesApplierSpec extends ObjectBehavior
{
    function let(
        ObjectUpdaterInterface $productUpdater,
        GetNonViewableCategoryCodes $getNonViewableCategoryCodes
    ) {
        $this->beConstructedWith($productUpdater, $getNonViewableCategoryCodes);
    }

    function it_is_an_user_intent_applier()
    {
        $this->shouldHaveType(SetCategoriesApplier::class);
        $this->shouldImplement(UserIntentApplier::class);
    }

    function it_applies_a_set_categories_user_intent_on_a_new_product(
        ObjectUpdaterInterface $productUpdater,
        GetNonViewableCategoryCodes $getNonViewableCategoryCodes
    ) {
        $userIntent = new SetCategories(['categoryA', 'categoryB']);
        $product = new Product();
        $product->setIdentifier('foo');

        $getNonViewableCategoryCodes->fromProductUuids([$product->getUuid()], 10)->willReturn([]);

        $productUpdater->update($product, ['categories' => ['categoryA', 'categoryB']])->shouldBeCalledOnce();

        $this->apply($userIntent, $product, 10);
    }
}
