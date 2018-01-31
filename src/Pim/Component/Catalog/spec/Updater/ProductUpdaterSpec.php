<?php

namespace spec\Pim\Component\Catalog\Updater;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\Component\StorageUtils\Updater\PropertySetterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Updater\ProductUpdater;

class ProductUpdaterSpec extends ObjectBehavior
{
    function let(
        PropertySetterInterface $propertySetter,
        ObjectUpdaterInterface $valuesUpdater
    ) {
        $this->beConstructedWith(
            $propertySetter,
            $valuesUpdater,
            ['enabled', 'family', 'categories', 'groups', 'associations'],
            ['identifier', 'created', 'updated']
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductUpdater::class);
    }

    function it_is_a_updater()
    {
        $this->shouldImplement(ObjectUpdaterInterface::class);
    }

    function it_throws_an_exception_when_trying_to_update_anything_else_than_a_product()
    {
        $this->shouldThrow(
            InvalidObjectException::objectExpected(
                'stdClass',
                'Pim\Component\Catalog\Model\ProductInterface'
            )
        )->during(
            'update',
            [new \stdClass(), []]
        );
    }

    function it_updates_a_product($propertySetter, $valuesUpdater, ProductInterface $product)
    {
        $values = [
            'name' => [['data' => 'newname', 'locale' => null, 'scope' => null]],
            'desc' => [['data' => 'newdescUS', 'locale' => 'en_US', 'scope' => null]],
        ];

        $propertySetter
            ->setData($product, 'groups', ['related1', 'related2'])
            ->shouldBeCalled();

        $valuesUpdater
            ->update($product, $values, [])
            ->shouldBeCalled();

        $updates = [
            'groups' => ['related1', 'related2'],
            'values' => $values
        ];

        $this->update($product, $updates, []);
    }

    function it_ignores_fields_when_updating_a_product($propertySetter, $valuesUpdater, ProductInterface $product)
    {
        $values = [
            'name' => [['data' => 'newname', 'locale' => null, 'scope' => null]],
            'desc' => [['data' => 'newdescUS', 'locale' => 'en_US', 'scope' => null]],
        ];

        $propertySetter
            ->setData($product, 'groups', ['related1', 'related2'])
            ->shouldBeCalled();

        $valuesUpdater
            ->update($product, $values, [])
            ->shouldBeCalled();

        $propertySetter
            ->setData($product, 'created', '2016-06-14T13:12:50+02:00')
            ->shouldNotBeCalled();

        $propertySetter
            ->setData($product, 'updated', '2016-06-14T13:12:50+02:00')
            ->shouldNotBeCalled();

        $updates = [
            'created'    => '2016-06-14T13:12:50+02:00',
            'updated'    => '2016-06-14T13:12:50+02:00',
            'identifier' => 'foo',
            'groups'     => ['related1', 'related2'],
            'values'     => $values,
        ];

        $this->update($product, $updates, []);
    }

    function it_throws_an_exception_when_updating_an_unknown_property(ProductInterface $product)
    {
        $updates = [
            'unknown_property' => 'foo',
        ];

        $this
            ->shouldThrow(UnknownPropertyException::unknownProperty('unknown_property'))
            ->during('update', [$product, $updates, []]);
    }

    function it_throws_an_exception_when_giving_a_non_scalar_enabled(
        ProductInterface $product
    ) {
        $this->shouldThrow(
            InvalidPropertyTypeException::class
        )->during('update', [$product, ['enabled' => []]]);
    }

    function it_throws_an_exception_when_giving_a_non_scalar_family(
        ProductInterface $product
    ) {
        $this->shouldThrow(
            InvalidPropertyTypeException::class
        )->during('update', [$product, ['family' => []]]);
    }

    function it_throws_an_exception_when_giving_a_non_scalar_parent(
        ProductInterface $product
    ) {
        $this->shouldThrow(
            InvalidPropertyTypeException::class
        )->during('update', [$product, ['parent' => []]]);
    }

    function it_throws_an_exception_when_giving_an_array_of_categories_with_non_scalar_values(
        ProductInterface $product
    ) {
        $this->shouldThrow(
            InvalidPropertyTypeException::class
        )->during('update', [$product, ['categories' => [[]]]]);
    }

    function it_throws_an_exception_when_giving_an_array_of_groups_with_non_scalar_values(
        ProductInterface $product
    ) {
        $this->shouldThrow(
            InvalidPropertyTypeException::class
        )->during('update', [$product, ['groups' => [[]]]]);
    }

    function it_throws_an_exception_when_giving_not_an_array_of_associations(
        ProductInterface $product
    ) {
        $this->shouldThrow(
            InvalidPropertyTypeException::class
        )->during('update', [$product, ['associations' => 'assoc']]);
    }

    function it_throws_an_exception_when_giving_an_array_of_associations_with_non_scalar_values(
        ProductInterface $product
    ) {
        $this->shouldThrow(
            InvalidPropertyTypeException::class
        )->during('update', [$product, ['associations' => ['assoc' => 'not_an_array']]]);
    }
}
