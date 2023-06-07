<?php

namespace spec\Akeneo\Test\Acceptance\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\Group;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\IdentifierValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Test\Acceptance\Common\NotImplementedException;
use Akeneo\Test\Acceptance\Product\InMemoryProductRepository;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;

class InMemoryProductRepositorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(InMemoryProductRepository::class);
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
        $attribute = new Attribute();
        $attribute->setCode('my_attribute');
        $product->addValue(ScalarValue::value($attribute, 'a-product'));
        $product->addValue(IdentifierValue::value('sku', true,'a-product'));
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
        $attribute = new Attribute();
        $attribute->setCode('my_attribute');
        $product->addValue(ScalarValue::value($attribute, 'a-product'));
        $product->addValue(IdentifierValue::value('sku', true, 'a-product'));

        $this->save($product)->shouldReturn(null);

        $this->findOneByIdentifier($product->getIdentifier())->shouldReturn($product);
    }

    function it_saves_only_products()
    {
        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('save', ['a_thing']);
    }

    function it_finds_a_product_from_its_id()
    {
        $product = new Product();
        $this->save($product);

        $this->find($product->getUuid())->shouldReturn($product);
    }

    function it_returns_null_when_it_does_not_find_a_product()
    {
        $this->find(mt_rand())->shouldReturn(null);
    }

    function it_finds_all_products()
    {
        $product1 = new Product();
        $product1->addValue(IdentifierValue::value('sku', true, 'product-1'));

        $this->save($product1);

        $product2 = new Product();
        $product2->addValue(IdentifierValue::value('sku', true, 'product-2'));
        $this->save($product2);

        $this->findAll()->shouldReturn(['product-1' => $product1, 'product-2' => $product2]);
    }

    function it_asserts_that_the_other_methods_are_not_implemented_yet()
    {
        $this->shouldThrow(NotImplementedException::class)->during('getClassName', []);
        $this->shouldThrow(NotImplementedException::class)->during('getAvailableAttributeIdsToExport', [[]]);
        $this->shouldThrow(NotImplementedException::class)->during('getProductsByGroup', [new Group(), 10]);
        $this->shouldThrow(NotImplementedException::class)->during('getProductCountByGroup', [new Group()]);
        $this->shouldThrow(NotImplementedException::class)->during('countAll', []);
        $this->shouldThrow(NotImplementedException::class)->during('hasAttributeInFamily',
            ['a-product', 'an-attribute']);
        $this->shouldThrow(NotImplementedException::class)->during('searchAfter', [null, 89]);
    }

    function it_returns_all_products()
    {
        $product1 = new Product();
        $product1->addValue(IdentifierValue::value('sku', true, 'a-product'));

        $this->save($product1);

        $product2 = new Product();
        $product2->addValue(IdentifierValue::value('sku', true, 'a-second-product'));
        $this->save($product2);

        $products = $this->findAll();
        $products->shouldBeArray();
        $products->shouldHaveCount(2);
        $products['a-product']->shouldBe($product1);
        $products['a-second-product']->shouldBe($product2);
    }

    function it_returns_products_from_identifiers()
    {
        foreach (['A', 'B', 'C'] as $identifier) {
            $product = new Product();
            $product->addValue(IdentifierValue::value('sku', true, $identifier));
            $this->save($product);
        }

        $products = $this->getItemsFromIdentifiers(['A', 'B']);
        $products->shouldBeArray();
        $products->shouldHaveCount(2);
        $products[0]->getIdentifier()->shouldBe('A');
        $products[1]->getIdentifier()->shouldBe('B');
    }

    function it_finds_products_by_criteria()
    {
        $productA = new Product();
        $productA->addValue(IdentifierValue::value('sku', true, 'A'));
        $this->save($productA);

        $productB = new Product();
        $productB->addValue(IdentifierValue::value('sku', true, 'B'));
        $this->save($productB);

        $products = $this->findBy(['identifier' => 'A']);
        $products->shouldBeArray();
        $products->shouldHaveCount(1);
        $products->shouldHaveKeyWithValue('A', $productA);
    }

    function it_finds_one_product_by_uuid()
    {
        $productA = new Product();
        $productA->addValue(IdentifierValue::value('sku', true, 'A'));
        $this->save($productA);

        $productB = new Product();
        $productB->addValue(IdentifierValue::value('sku', true, 'B'));
        $this->save($productB);

        $this->findOneBy(['uuid' => $productA->getUuid()])->shouldBe($productA);
        $this->findOneBy(['uuid' => $productB->getUuid()])->shouldBe($productB);
        $this->findOneBy(['uuid' => Uuid::uuid4()])->shouldBeNull();
    }

    function it_gets_products_by_uuids()
    {
        $product1 = new Product();
        $product1->addValue(IdentifierValue::value('sku', true, 'foo'));
        $this->save($product1);
        $product2 = new Product();
        $product2->addValue(IdentifierValue::value('sku', true, 'bar'));
        $this->save($product2);

        $this->getItemsFromUuids(
            [
                $product1->getUuid()->toString(),
                'not_a_uuid',
                Uuid::uuid4()->toString(),
                $product2->getUuid()->toString(),
            ]
        )->shouldReturn([$product1, $product2]);
    }
}
