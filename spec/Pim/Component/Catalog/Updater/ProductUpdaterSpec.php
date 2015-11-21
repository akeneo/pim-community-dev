<?php

namespace spec\Pim\Component\Catalog\Updater;

use Akeneo\Component\StorageUtils\Updater\PropertyCopierInterface;
use Akeneo\Component\StorageUtils\Updater\PropertySetterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Component\Catalog\Updater\ProductTemplateUpdaterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductUpdaterSpec extends ObjectBehavior
{
    function let(
        PropertySetterInterface $propertySetter,
        PropertyCopierInterface $propertyCopier,
        ProductTemplateUpdaterInterface $templateUpdater,
        ValidatorInterface $validator
    ) {
        $this->beConstructedWith($propertySetter, $propertyCopier, $templateUpdater, $validator);
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
        $this->shouldThrow(new \InvalidArgumentException('Expects a "Pim\Bundle\CatalogBundle\Model\ProductInterface", "stdClass" provided.'))->during(
            'update', [new \stdClass(), []]
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
            'name' => [['data' => 'newname', 'locale' => null, 'scope' => null]],
            'desc' => [['data' => 'newdescUS', 'locale' => 'en_US', 'scope' => null]],
        ];

        $this->update($product, $updates, []);
    }
}
