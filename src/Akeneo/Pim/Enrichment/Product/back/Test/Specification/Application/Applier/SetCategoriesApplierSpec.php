<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\Application\Applier;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\Application\Applier\SetCategoriesApplier;
use Akeneo\Pim\Enrichment\Product\Domain\Model\ProductIdentifier;
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

    function it_is_initializable()
    {
        $this->shouldHaveType(SetCategoriesApplier::class);
    }

    function it_applies_a_set_categories_user_intent_on_a_new_product(
        ObjectUpdaterInterface $productUpdater,
        GetNonViewableCategoryCodes $getNonViewableCategoryCodes
    ) {
        $userIntent = new SetCategories(['categoryA', 'categoryB']);
        $product = new Product();
        $product->setIdentifier('foo');

        $getNonViewableCategoryCodes->fromProductIdentifiers([ProductIdentifier::fromString('foo')], 10)->willReturn([]);

        $productUpdater->update($product, ['categories' => ['categoryA', 'categoryB']])->shouldBeCalledOnce();

        $this->apply($product, $userIntent, 10);
    }

    function it_applies_a_set_categories_user_intent_when_all_product_categories_are_viewable(
        ObjectUpdaterInterface $productUpdater,
        GetNonViewableCategoryCodes $getNonViewableCategoryCodes
    ) {
        $userIntent = new SetCategories(['categoryA', 'categoryB']);
        $product = new Product();
        $product->setIdentifier('foo');

        $getNonViewableCategoryCodes->fromProductIdentifiers([ProductIdentifier::fromString('foo')], 10)
            ->willReturn(['foo' => []]);

        $productUpdater->update($product, ['categories' => ['categoryA', 'categoryB']])->shouldBeCalledOnce();

        $this->apply($product, $userIntent, 10);
    }

    function it_merges_non_viewable_categories_when_applying_a_set_categories_user_intent(
        ObjectUpdaterInterface $productUpdater,
        GetNonViewableCategoryCodes $getNonViewableCategoryCodes
    ) {
        $userIntent = new SetCategories(['categoryA', 'categoryB']);
        $product = new Product();
        $product->setIdentifier('foo');

        $getNonViewableCategoryCodes->fromProductIdentifiers([ProductIdentifier::fromString('foo')], 10)
            ->willReturn(['foo' => ['categoryD', 'categoryE']]);

        $productUpdater->update($product, ['categories' => ['categoryA', 'categoryB', 'categoryD', 'categoryE']])
            ->shouldBeCalledOnce();

        $this->apply($product, $userIntent, 10);
    }
}
