<?php

namespace spec\Pim\Component\Catalog\Association;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\Product;
use Pim\Component\Catalog\Model\ProductAssociation;
use Pim\Component\Catalog\Model\ProductModel;
use Pim\Component\Catalog\Model\ProductModelAssociation;

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
            'Pim\Component\Catalog\Model\Product' => 'Pim\Component\Catalog\Model\ProductAssociation',
            'Pim\Component\Catalog\Model\ProductModel' => 'Pim\Component\Catalog\Model\ProductModelAssociation',

        ]);
    }

    function it_gives_the_right_association_class()
    {
        $entity = new Product();
        $this->resolveAssociationClass($entity)
            ->shouldReturn(ProductAssociation::class);

        $entity = new ProductModel();
        $this->resolveAssociationClass($entity)
            ->shouldReturn(ProductModelAssociation::class);
    }
}
