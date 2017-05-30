<?php

namespace spec\Pim\Component\Catalog\Updater;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Component\StorageUtils\Updater\PropertySetterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Updater\ProductTemplateUpdaterInterface;
use Pim\Component\Catalog\Updater\ProductUpdater;

class ProductUpdaterSpec extends ObjectBehavior
{
    function let(
        PropertySetterInterface $propertySetter,
        ProductTemplateUpdaterInterface $templateUpdater
    ) {
        $this->beConstructedWith(
            $propertySetter,
            $templateUpdater,
            ['enabled', 'family', 'categories', 'variant_group', 'groups', 'associations'],
            ['identifier', 'created', 'updated']
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Updater\ProductUpdater');
    }

    function it_is_a_updater()
    {
        $this->shouldImplement('Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface');
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

    function it_updates_a_product($propertySetter, ProductInterface $product)
    {
        $propertySetter
            ->setData($product, 'groups', ['related1', 'related2'])
            ->shouldBeCalled();
        $propertySetter
            ->setData($product, 'name', 'newname', ['locale' => null, 'scope' => null])
            ->shouldBeCalled();
        $propertySetter
            ->setData($product, 'desc', 'newdescUS', ['locale' => 'en_US', 'scope' => null])
            ->shouldBeCalled();

        $updates = [
            'groups' => ['related1', 'related2'],
            'values' => [
                'name' => [['data' => 'newname', 'locale' => null, 'scope' => null]],
                'desc' => [['data' => 'newdescUS', 'locale' => 'en_US', 'scope' => null]],
            ]
        ];

        $this->update($product, $updates, []);
    }

    function it_ignores_fields_when_updating_a_product($propertySetter, ProductInterface $product)
    {
        $propertySetter
            ->setData($product, 'groups', ['related1', 'related2'])
            ->shouldBeCalled();
        $propertySetter
            ->setData($product, 'name', 'newname', ['locale' => null, 'scope' => null])
            ->shouldBeCalled();
        $propertySetter
            ->setData($product, 'desc', 'newdescUS', ['locale' => 'en_US', 'scope' => null])
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
            'values'     => [
                'name' => [['data' => 'newname', 'locale' => null, 'scope' => null]],
                'desc' => [['data' => 'newdescUS', 'locale' => 'en_US', 'scope' => null]],
            ],
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

    function it_throws_an_exception_when_values_is_not_an_array(ProductInterface $product)
    {
        $updates = [
            'values' => 'foo',
        ];

        $this
            ->shouldThrow(InvalidPropertyTypeException::arrayExpected('values', ProductUpdater::class, 'foo'))
            ->during('update', [$product, $updates, []]);
    }

    function it_throws_an_exception_when_it_is_not_an_array_of_product_values(ProductInterface $product)
    {
        $updates = [
            'values'     => [
                'name' => 'foo',
            ],
        ];

        $this
            ->shouldThrow(InvalidPropertyTypeException::arrayExpected('name', ProductUpdater::class, 'foo'))
            ->during('update', [$product, $updates, []]);
    }

    function it_throws_an_exception_when_it_a_product_value_is_not_an_array(ProductInterface $product)
    {
        $updates = [
            'values'     => [
                'name' => ['foo'],
            ],
        ];

        $this
            ->shouldThrow(
                InvalidPropertyTypeException::validArrayStructureExpected(
                    'name',
                    'one of the product values is not an array.',
                    ProductUpdater::class,
                    ['foo']
                )
            )
            ->during('update', [$product, $updates, []]);
    }

    function it_throws_an_exception_when_locale_is_missing_in_a_product_value(ProductInterface $product)
    {
        $updates = [
            'values'     => [
                'name' => [['scope' => null, 'data' => null]],
            ],
        ];

        $this
            ->shouldThrow(InvalidPropertyTypeException::arrayKeyExpected('name', 'locale', ProductUpdater::class, ['scope' => null, 'data' => null]))
            ->during('update', [$product, $updates, []]);
    }

    function it_throws_an_exception_when_scope_is_missing_in_a_product_value(ProductInterface $product)
    {
        $updates = [
            'values'     => [
                'name' => [['locale' => null, 'data' => null]],
            ],
        ];

        $this
            ->shouldThrow(InvalidPropertyTypeException::arrayKeyExpected('name', 'scope', ProductUpdater::class, ['locale' => null, 'data' => null]))
            ->during('update', [$product, $updates, []]);
    }

    function it_throws_an_exception_when_locale_is_not_a_string_in_the_product_value(ProductInterface $product)
    {
        $updates = [
            'values'     => [
                'name' => [['locale' => [], 'scope' => null, 'data' => null]],
            ],
        ];

        $this
            ->shouldThrow(
                 new InvalidPropertyTypeException(
                    'name',
                    [],
                    ProductUpdater::class,
                    'Property "name" expects a product value with a string as locale, "array" given.',
                    InvalidPropertyTypeException::STRING_EXPECTED_CODE
                )
            )
            ->during('update', [$product, $updates, []]);
    }

    function it_throws_an_exception_when_scope_is_not_a_string_in_the_product_value(ProductInterface $product)
    {
        $updates = [
            'values'     => [
                'name' => [['locale' => null, 'scope' => [], 'data' => null]],
            ],
        ];

        $this
            ->shouldThrow(
                new InvalidPropertyTypeException(
                    'name',
                    [],
                    ProductUpdater::class,
                    'Property "name" expects a product value with a string as scope, "array" given.',
                    InvalidPropertyTypeException::STRING_EXPECTED_CODE
                )
            )
            ->during('update', [$product, $updates, []]);
    }
}
