<?php

namespace spec\Pim\Component\Catalog\Updater;

use Akeneo\Component\StorageUtils\Updater\PropertyCopierInterface;
use Akeneo\Component\StorageUtils\Updater\PropertySetterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductTemplateInterface;
use Pim\Component\Catalog\Updater\ProductTemplateUpdaterInterface;
use Prophecy\Argument;
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
        $this->shouldThrow(new \InvalidArgumentException('Expects a "Pim\Component\Catalog\Model\ProductInterface", "stdClass" provided.'))->during(
            'update', [new \stdClass(), []]
        );
    }

    function it_updates_a_new_product_without_a_family(
        $propertySetter,
        ProductInterface $product
    ) {
        $product->getFamily()->willReturn(null);
        $product->getVariantGroup()->willReturn(null);
        $product->getCreated()->willReturn(null);
        $product->setCreated(Argument::type('\DateTime'))->shouldBeCalled();
        $product->setUpdated(Argument::type('\DateTime'))->shouldBeCalled();

        $product->getValue('name', null, null)->willReturn(true);
        $product->getValue('desc', 'en_US', null)->willReturn(true);

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
            'name'   => [['data' => 'newname', 'locale' => null, 'scope' => null]],
            'desc'   => [['data' => 'newdescUS', 'locale' => 'en_US', 'scope' => null]],
        ];

        $this->update($product, $updates, []);
    }

    function it_updates_an_existing_product_without_a_family(
        $propertySetter,
        ProductInterface $product,
        \DateTime $date
    ) {
        $product->getFamily()->willReturn(null);
        $product->getVariantGroup()->willReturn(null);
        $product->getCreated()->willReturn($date);
        $product->setUpdated(Argument::type('\DateTime'))->shouldBeCalled();

        $product->getValue('name', null, null)->willReturn(true);
        $product->getValue('desc', 'en_US', null)->willReturn(true);

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
            'name'   => [['data' => 'newname', 'locale' => null, 'scope' => null]],
            'desc'   => [['data' => 'newdescUS', 'locale' => 'en_US', 'scope' => null]],
        ];

        $this->update($product, $updates, []);
    }

    function it_updates_an_existing_product_with_a_family(
        $propertySetter,
        ProductInterface $product,
        FamilyInterface $family,
        \DateTime $date
    ) {
        $family->getAttributeCodes()->willReturn(['attribut_family_1']);

        $product->getFamily()->willReturn($family);
        $product->getVariantGroup()->willReturn(null);
        $product->getCreated()->willReturn($date);
        $product->setUpdated(Argument::type('\DateTime'))->shouldBeCalled();

        $product->getValue('attribut_family_1', null, null)
            ->willReturn(['data' => 0, 'locale' => null, 'scope' => null]);

        $propertySetter
            ->setData($product, 'attribut_family_1', 1, ['locale' => null, 'scope' => null])
            ->shouldBeCalled();

        $updates = [
            'attribut_family_1' => [['data' => 1, 'locale' => null, 'scope' => null]],
        ];

        $this->update($product, $updates, []);
    }

    function it_updates_the_product_group_values(
        $propertySetter,
        $templateUpdater,
        ProductInterface $product,
        GroupInterface $variantGroup,
        ProductTemplateInterface $productTemplate,
        \DateTime $date
    ) {
        $productTemplate->hasValueForAttributeCode('variant_attribute')->willReturn(true);
        $templateUpdater->update($productTemplate, [$product]);
        $variantGroup->getProductTemplate()->willReturn($productTemplate);

        $product->getFamily()->willReturn(null);
        $product->getVariantGroup()->willReturn($variantGroup);
        $product->getCreated()->willReturn($date);
        $product->setUpdated(Argument::type('\DateTime'))->shouldBeCalled();

        $product->getValue('variant_attribute', null, null)
            ->willReturn(['data' => 0, 'locale' => null, 'scope' => null]);

        $propertySetter
            ->setData($product, 'variant_attribute', 1, ['locale' => null, 'scope' => null])
            ->shouldBeCalled();

        $updates = [
            'variant_attribute' => [['data' => 1, 'locale' => null, 'scope' => null]],
        ];

        $this->update($product, $updates, []);
    }

    function it_does_not_update_updated_at_attribute_when_no_updates_are_made(
        ProductInterface $product,
        \DateTime $date
    ) {
        $product->getVariantGroup()->willReturn(null);

        $product->getCreated()->willReturn($date);
        $product->setUpdated(Argument::any())->shouldNotBeCalled();

        $updates = [];
        $this->update($product, $updates, []);
    }
}
