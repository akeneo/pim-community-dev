<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Updater\Clearer\Field;

use Akeneo\Pim\Enrichment\Component\Category\Model\Category;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Clearer\ClearerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Webmozart\Assert\Assert;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryFieldClearerSpec extends ObjectBehavior
{
    function it_is_a_clearer()
    {
        $this->shouldImplement(ClearerInterface::class);
    }

    function it_supports_only_categories_field()
    {
        $this->supportsProperty('categories')->shouldReturn(true);
        $this->supportsProperty('groups')->shouldReturn(false);
        $this->supportsProperty('other')->shouldReturn(false);
    }

    function it_removes_all_categories_of_a_product()
    {
        $product = new Product();
        $categories = new ArrayCollection();
        $categories->add(new Category());
        $categories->add(new Category());
        $product->setCategories($categories);
        Assert::count($product->getCategories(), 2);

        $this->clear($product, 'categories');
        Assert::count($product->getCategories(), 0);
    }

    function it_removes_all_categories_of_a_product_model()
    {
        $productModel = new ProductModel();
        $categories = new ArrayCollection();
        $categories->add(new Category());
        $categories->add(new Category());
        $productModel->setCategories($categories);
        Assert::count($productModel->getCategories(), 2);

        $this->clear($productModel, 'categories');
        Assert::count($productModel->getCategories(), 0);
    }
}
