<?php

namespace spec\Akeneo\Test\Acceptance\Catalog\Product\Model;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Test\Common\NotImplementedException;
use Akeneo\Test\Acceptance\Catalog\Product\Model\InMemoryProductModelRepository;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\FamilyVariant;
use Pim\Component\Catalog\Model\ProductModel;
use Pim\Component\Catalog\Repository\ProductModelRepositoryInterface;
use Prophecy\Argument;

class InMemoryProductModelRepositorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(InMemoryProductModelRepository::class);
    }

    function it_is_an_identifiable_repository()
    {
        $this->shouldBeAnInstanceOf(IdentifiableObjectRepositoryInterface::class);
    }

    function it_is_a_saver()
    {
        $this->shouldBeAnInstanceOf(SaverInterface::class);
    }

    function it_is_a_product_model_repository()
    {
        $this->shouldBeAnInstanceOf(ProductModelRepositoryInterface::class);
    }

    function it_asserts_the_identifier_property_is_the_code()
    {
        $this->getIdentifierProperties()->shouldReturn(['code']);
    }

    function it_finds_a_product_model_by_identifier()
    {
        $productModel = new ProductModel();
        $productModel->setCode('a-product-model');
        $this->beConstructedWith([$productModel->getCode() => $productModel]);

        $this->findOneByIdentifier('a-product-model')->shouldReturn($productModel);
    }

    function it_finds_nothing_if_it_does_not_exist()
    {
        $this->findOneByIdentifier('a-non-existing-product-models')->shouldReturn(null);
    }

    function it_saves_a_family_variant()
    {
        $productModel = new ProductModel();
        $productModel->setCode('a-product-model');

        $this->save($productModel)->shouldReturn(null);

        $this->findOneByIdentifier($productModel->getCode())->shouldReturn($productModel);
    }

    function it_saves_only_product_models()
    {
        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('save', ['a_thing']);
    }

    function it_asserts_that_the_other_methods_are_not_implemented_yet()
    {
        $productModel = new ProductModel();
        $productModel->setCode('a-product-model');

        $familyVariant = new FamilyVariant();

        $this->shouldThrow(NotImplementedException::class)->during('getItemsFromIdentifiers', [[]]);
        $this->shouldThrow(NotImplementedException::class)->during('find', ['']);
        $this->shouldThrow(NotImplementedException::class)->during('findAll', []);
        $this->shouldThrow(NotImplementedException::class)->during('findBy', [[]]);
        $this->shouldThrow(NotImplementedException::class)->during('findOneBy', [[]]);
        $this->shouldThrow(NotImplementedException::class)->during('getClassName', []);
        $this->shouldThrow(NotImplementedException::class)->during('findSiblingsProductModels', [$productModel]);
        $this->shouldThrow(NotImplementedException::class)->during('countRootProductModels', []);
        $this->shouldThrow(NotImplementedException::class)->during('findChildrenProductModels', [$productModel]);
        $this->shouldThrow(NotImplementedException::class)->during('findDescendantProductIdentifiers', [$productModel]);
        $this->shouldThrow(NotImplementedException::class)->during('findByIdentifiers', [[]]);
        $this->shouldThrow(NotImplementedException::class)->during('findChildrenProducts', [$productModel]);
        $this->shouldThrow(NotImplementedException::class)->during('searchRootProductModelsAfter', [null, 5]);
        $this->shouldThrow(NotImplementedException::class)->during('findSubProductModels', [$familyVariant]);
        $this->shouldThrow(NotImplementedException::class)->during('findRootProductModels', [$familyVariant]);
        $this->shouldThrow(NotImplementedException::class)->during('searchLastLevelByCode', [$familyVariant, '', 1]);
    }
}
