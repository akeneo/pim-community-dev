<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\Application\Applier;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\AddCategories;
use Akeneo\Pim\Enrichment\Product\Application\Applier\AddCategoriesApplier;
use Akeneo\Pim\Enrichment\Product\Application\Applier\UserIntentApplier;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetCategoryCodes;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;

class AddCategoriesApplierSpec extends ObjectBehavior
{
    function let(ObjectUpdaterInterface $productUpdater, GetCategoryCodes $getCategoryCodes)
    {
        $this->beConstructedWith($productUpdater, $getCategoryCodes);
    }

    function it_is_an_user_intent_applier()
    {
        $this->shouldHaveType(AddCategoriesApplier::class);
        $this->shouldImplement(UserIntentApplier::class);
    }

    function it_supports_add_category_user_intent()
    {
        $this->getSupportedUserIntents()->shouldReturn([AddCategories::class]);
    }

    function it_adds_categories_on_an_uncategorized_product(
        ObjectUpdaterInterface $productUpdater,
        GetCategoryCodes $getCategoryCodes
    ) {
        $product = new Product();
        $getCategoryCodes->fromProductUuids([$product->getUuid()])
            ->willReturn([$product->getUuid()->toString() => []]);

        $productUpdater->update($product, ['categories' => ['supplier', 'print']])->shouldBeCalledOnce();

        $this->apply(new AddCategories(['supplier', 'print']), $product, 10);
    }

    function it_adds_categories_on_an_categorized_product(
        ObjectUpdaterInterface $productUpdater,
        GetCategoryCodes $getCategoryCodes
    ) {
        $product = new Product();
        $getCategoryCodes->fromProductUuids([$product->getUuid()])
            ->willReturn([$product->getUuid()->toString() => ['print', 'master']]);

        $productUpdater->update($product, ['categories' => ['print', 'master', 'supplier']])->shouldBeCalledOnce();

        $this->apply(new AddCategories(['supplier', 'print']), $product, 10);
    }

    function it_adds_categories_on_an_unknown_product(
        ObjectUpdaterInterface $productUpdater,
        GetCategoryCodes $getCategoryCodes
    ) {
        $product = new Product();
        $getCategoryCodes->fromProductUuids([$product->getUuid()])
            ->willReturn([]);

        $productUpdater->update($product, ['categories' => ['supplier', 'print']])->shouldBeCalledOnce();

        $this->apply(new AddCategories(['supplier', 'print']), $product, 10);
    }
}
