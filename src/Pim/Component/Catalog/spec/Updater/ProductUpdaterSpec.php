<?php

namespace spec\Pim\Component\Catalog\Updater;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Component\StorageUtils\Updater\PropertySetterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Updater\ProductTemplateUpdaterInterface;

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

    function it_ignores_fields__when_updating_a_product($propertySetter, ProductInterface $product)
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
}
