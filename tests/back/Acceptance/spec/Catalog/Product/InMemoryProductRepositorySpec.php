<?php

namespace spec\Akeneo\Test\Acceptance\Catalog\Product;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Test\Acceptance\Common\NotImplementedException;
use Akeneo\Test\Acceptance\Catalog\Product\InMemoryProductRepository;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Component\Catalog\Model\Product;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use Pim\Component\Catalog\Value\ScalarValue;
use Prophecy\Argument;

class InMemoryProductRepositorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(\Akeneo\Test\Acceptance\Catalog\Product\InMemoryProductRepository::class);
    }

    function it_is_an_identifiable_repository()
    {
        $this->shouldBeAnInstanceOf(IdentifiableObjectRepositoryInterface::class);
    }

    function it_is_a_saver()
    {
        $this->shouldBeAnInstanceOf(SaverInterface::class);
    }

    function it_is_a_product_repository()
    {
        $this->shouldBeAnInstanceOf(ProductRepositoryInterface::class);
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
