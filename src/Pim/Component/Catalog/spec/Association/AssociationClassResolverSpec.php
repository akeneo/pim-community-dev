<?php

namespace spec\Pim\Component\Catalog\Association;

use InvalidArgumentException;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\EntityWithAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductAssociation;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelAssociation;

/**
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationClassResolverSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith([
            'Akeneo\Pim\Enrichment\Component\Product\Model\Product' => 'Akeneo\Pim\Enrichment\Component\Product\Model\ProductAssociation',
            'Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel' => 'Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelAssociation',
        ]);
    }

    function it_gives_the_right_association_class(
        Product $product,
        ProductModel $productModel
    ) {
        $this->resolveAssociationClass($product)
            ->shouldReturn(ProductAssociation::class);

        $this->resolveAssociationClass($productModel)
            ->shouldReturn(ProductModelAssociation::class);
    }

    function it_throws_an_exception_if_no_association_class_is_found_for_the_entity(
        EntityWithAssociationsInterface $entity
    ) {
        $this->shouldThrow(InvalidArgumentException::class)->during('resolveAssociationClass', [$entity]);
    }
}
