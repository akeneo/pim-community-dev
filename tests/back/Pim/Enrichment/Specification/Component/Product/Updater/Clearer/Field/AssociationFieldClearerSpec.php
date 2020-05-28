<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Updater\Clearer\Field;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductAssociation;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelAssociation;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Clearer\ClearerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Webmozart\Assert\Assert;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationFieldClearerSpec extends ObjectBehavior
{
    function it_is_a_clearer()
    {
        $this->shouldImplement(ClearerInterface::class);
    }

    function it_supports_only_associations_field()
    {
        $this->supportsProperty('categories')->shouldReturn(false);
        $this->supportsProperty('associations')->shouldReturn(true);
        $this->supportsProperty('other')->shouldReturn(false);
    }

    function it_removes_all_association_of_a_product()
    {
        $product = new Product();
        $associations = new ArrayCollection();
        $associations->add(new ProductAssociation());
        $associations->add(new ProductAssociation());
        $product->setAssociations($associations);

        $this->clear($product, 'associations');
        Assert::same($this->getAssociationsCount($product), 0);
    }

    function it_removes_all_association_of_a_product_model()
    {
        $productModel = new ProductModel();
        $associations = new ArrayCollection();
        $associations->add(new ProductModelAssociation());
        $associations->add(new ProductModelAssociation());
        $productModel->setAssociations($associations);

        $this->clear($productModel, 'associations');
        Assert::same($this->getAssociationsCount($productModel), 0);
    }

    private function getAssociationsCount(EntityWithAssociationsInterface $entity): int
    {
        $count = 0;
        foreach ($entity->getAssociations() as $association) {
            $count += $association->getProducts()->count();
            $count += $association->getProductModels()->count();
            $count += $association->getGroups()->count();
        };

        return $count;
    }
}
