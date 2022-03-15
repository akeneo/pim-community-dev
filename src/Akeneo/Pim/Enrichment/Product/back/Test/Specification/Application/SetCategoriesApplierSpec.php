<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\Application;

use Akeneo\Pim\Enrichment\Category\API\Query\GetViewableCategories;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\Application\SetCategoriesApplier;
use Akeneo\Pim\Enrichment\Product\Domain\Model\ProductIdentifier;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetCategoryCodes;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;

class SetCategoriesApplierSpec extends ObjectBehavior
{
    function let(
        ObjectUpdaterInterface $productUpdater,
        GetCategoryCodes $getCategoryCodes,
        GetViewableCategories $getViewableCategories
    ) {
        $this->beConstructedWith($productUpdater, $getCategoryCodes, $getViewableCategories);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SetCategoriesApplier::class);
    }

    function it_applies_a_set_categories_user_intent_on_a_new_product(
        ObjectUpdaterInterface $productUpdater,
        GetCategoryCodes $getCategoryCodes
    ) {
        $userIntent = new SetCategories(['categoryA', 'categoryB']);
        $product = new Product();
        $product->setIdentifier('foo');

        $getCategoryCodes->fromProductIdentifiers([ProductIdentifier::fromString('foo')])->willReturn([]);

        $productUpdater->update($product, ['categories' => ['categoryA', 'categoryB']])->shouldBeCalledOnce();

        $this->apply($product, $userIntent, 10);
    }

    function it_applies_a_set_categories_user_intent_on_an_uncategorized_product(
        ObjectUpdaterInterface $productUpdater,
        GetCategoryCodes $getCategoryCodes
    ) {
        $userIntent = new SetCategories(['categoryA', 'categoryB']);
        $product = new Product();
        $product->setIdentifier('foo');

        $getCategoryCodes->fromProductIdentifiers([ProductIdentifier::fromString('foo')])
            ->willReturn(['foo' => []]);

        $productUpdater->update($product, ['categories' => ['categoryA', 'categoryB']])->shouldBeCalledOnce();

        $this->apply($product, $userIntent, 10);
    }

    function it_merges_non_viewable_categories_when_applying_a_set_categories_user_intent_on_a_categorized_product(
        ObjectUpdaterInterface $productUpdater,
        GetCategoryCodes $getCategoryCodes,
        GetViewableCategories $getViewableCategories
    ) {
        $userIntent = new SetCategories(['categoryA', 'categoryB']);
        $product = new Product();
        $product->setIdentifier('foo');

        $getCategoryCodes->fromProductIdentifiers([ProductIdentifier::fromString('foo')])
            ->willReturn(['foo' => ['categoryC', 'categoryD', 'categoryE']]);
        $getViewableCategories->forUserId(['categoryC', 'categoryD', 'categoryE'], 10)->willReturn(['categoryC']);

        $productUpdater->update($product, ['categories' => ['categoryA', 'categoryB', 'categoryD', 'categoryE']])
            ->shouldBeCalledOnce();

        $this->apply($product, $userIntent, 10);
    }
}
