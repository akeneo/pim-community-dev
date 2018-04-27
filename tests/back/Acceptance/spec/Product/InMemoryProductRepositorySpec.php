<?php

namespace spec\Akeneo\Test\Acceptance\Product;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Test\Acceptance\Common\NotImplementedException;
use Akeneo\Test\Acceptance\Product\InMemoryProductRepository;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Component\Catalog\Model\Product;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use Pim\Component\Catalog\Value\ScalarValue;

class InMemoryProductRepositorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(InMemoryProductRepository::class);
    }

    function it_is_an_identifiable_repository()
    {
        $this->shouldImplement(IdentifiableObjectRepositoryInterface::class);
    }

    function it_is_a_saver()
    {
        $this->shouldImplement(SaverInterface::class);
    }

    function it_is_a_bulk_saver()
    {
        $this->shouldImplement(BulkSaverInterface::class);
    }

    function it_is_a_product_repository()
    {
        $this->shouldImplement(ProductRepositoryInterface::class);
    }

    function it_asserts_the_identifier_property_is_the_identifier()
    {
        $this->getIdentifierProperties()->shouldReturn(['identifier']);
    }

    function it_finds_a_product_by_identifier()
    {
        $product = new Product();
        $product->setIdentifier(new ScalarValue(new Attribute(), '', '', 'a-product'));
        $this->beConstructedWith([$product->getIdentifier() => $product]);

        $this->findOneByIdentifier('a-product')->shouldReturn($product);
    }

    function it_finds_nothing_if_it_does_not_exist()
    {
        $this->findOneByIdentifier('a-non-existing-product')->shouldReturn(null);
    }

    function it_saves_a_product()
    {
        $product = new Product();
        $product->setIdentifier(new ScalarValue(new Attribute(), '', '', 'a-product'));

        $this->save($product)->shouldReturn(null);

        $this->findOneByIdentifier($product->getIdentifier())->shouldReturn($product);
    }

    function it_saves_many_products()
    {
        $productA = new Product();
        $productB = new Product();
        $productC = new Product();
        $productA->setIdentifier(new ScalarValue(new Attribute(), '', '', 'product-a'));
        $productB->setIdentifier(new ScalarValue(new Attribute(), '', '', 'product-b'));
        $productC->setIdentifier(new ScalarValue(new Attribute(), '', '', 'product-c'));

        $this->saveAll([$productA, $productB, $productC])->shouldReturn(null);

        $this->findOneByIdentifier($productA->getIdentifier())->shouldReturn($productA);
        $this->findOneByIdentifier($productB->getIdentifier())->shouldReturn($productB);
        $this->findOneByIdentifier($productC->getIdentifier())->shouldReturn($productC);
    }

    function it_saves_only_products()
    {
        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('save', ['a_thing']);
    }

    function it_asserts_that_the_other_methods_are_not_implemented_yet()
    {
        $this->shouldThrow(NotImplementedException::class)->during('find', ['a-product']);
        $this->shouldThrow(NotImplementedException::class)->during('findAll', []);
        $this->shouldThrow(NotImplementedException::class)->during('findBy', [[]]);
        $this->shouldThrow(NotImplementedException::class)->during('findOneBy', [[]]);
        $this->shouldThrow(NotImplementedException::class)->during('getClassName', []);
        $this->shouldThrow(NotImplementedException::class)->during('getAvailableAttributeIdsToExport', [[]]);
        $this->shouldThrow(NotImplementedException::class)->during('getProductsByGroup', [new Group(), 10]);
        $this->shouldThrow(NotImplementedException::class)->during('getProductCountByGroup', [new Group()]);
        $this->shouldThrow(NotImplementedException::class)->during('countAll', []);
        $this->shouldThrow(NotImplementedException::class)->during('hasAttributeInFamily', ['a-product', 'an-attribute']);
        $this->shouldThrow(NotImplementedException::class)->during('searchAfter', [null, 89]);
    }
}
