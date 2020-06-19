<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier;

use Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\ClearerActionApplier;
use Akeneo\Pim\Automation\RuleEngine\Component\Event\SkippedActionForSubjectEvent;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductClearAction;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductSetAction;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Tool\Component\RuleEngine\ActionApplier\ActionApplierInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertyClearerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ClearerActionApplierSpec extends ObjectBehavior
{
    function let(PropertyClearerInterface $propertyClearer, GetAttributes $getAttributes, EventDispatcherInterface $eventDispatcher)
    {
        $this->beConstructedWith($propertyClearer, $getAttributes, $eventDispatcher);
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(ClearerActionApplier::class);
    }

    function it_is_an_action_applier()
    {
        $this->shouldBeAnInstanceOf(ActionApplierInterface::class);
    }

    function it_supports_only_clearer_action()
    {
        $clearerAction = new ProductClearAction(['field' => 'name']);
        $this->supports($clearerAction)->shouldBe(true);

        $setAction = new ProductSetAction(['field' => 'name', 'value' => 'whatever']);
        $this->supports($setAction)->shouldBe(false);
    }

    function it_applies_clear_attribute_action_on_non_variant_product(
        PropertyClearerInterface $propertyClearer,
        GetAttributes $getAttributes
    ) {
        $clearerAction = new ProductClearAction(['field' => 'name']);
        $product = new Product();

        $attribute = $this->buildAttribute('name');
        $getAttributes->forCode('name')->willReturn($attribute);

        $propertyClearer->clear(
            $product,
            'name',
            ['locale' => null, 'scope' => null]
        )->shouldBeCalled();

        $this->applyAction($clearerAction, [$product])->shouldReturn([$product]);
    }

    function it_applies_clear_attribute_action_with_locale_and_scope_on_non_variant_product(
        PropertyClearerInterface $propertyClearer,
        GetAttributes $getAttributes
    ) {
        $clearerAction = new ProductClearAction(['field' => 'name', 'locale' => 'en_US', 'scope' => 'ecommerce']);
        $product = new Product();

        $attribute = $this->buildAttribute('name');
        $getAttributes->forCode('name')->willReturn($attribute);

        $propertyClearer->clear(
            $product,
            'name',
            ['locale' => 'en_US', 'scope' => 'ecommerce']
        )->shouldBeCalled();

        $this->applyAction($clearerAction, [$product])->shouldReturn([$product]);
    }

    function it_applies_clear_attribute_action_on_product_model_when_attribute_is_at_same_level(
        PropertyClearerInterface $propertyClearer,
        GetAttributes $getAttributes,
        ProductModel $productModel,
        FamilyInterface $family,
        FamilyVariantInterface $familyVariant
    ) {
        $clearerAction = new ProductClearAction(['field' => 'name']);

        $attribute = $this->buildAttribute('name');
        $getAttributes->forCode('name')->willReturn($attribute);

        $productModel->getFamily()->willReturn($family);
        $family->hasAttributeCode('name')->willReturn(true);
        $productModel->getFamilyVariant()->willReturn(null);
        $productModel->getVariationLevel()->willReturn(1);
        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getLevelForAttributeCode('name')->willReturn(1);

        $propertyClearer->clear(
            $productModel,
            'name',
            ['locale' => null, 'scope' => null]
        )->shouldBeCalled();

        $this->applyAction($clearerAction, [$productModel])->shouldReturn([$productModel]);
    }

    function it_applies_clear_attribute_action_on_product_model_with_no_family_variant(
        PropertyClearerInterface $propertyClearer,
        GetAttributes $getAttributes,
        ProductModel $productModel,
        FamilyInterface $family,
        FamilyVariantInterface $familyVariant
    ) {
        $clearerAction = new ProductClearAction(['field' => 'name']);

        $attribute = $this->buildAttribute('name');
        $getAttributes->forCode('name')->willReturn($attribute);

        $productModel->getFamily()->willReturn($family);
        $family->hasAttributeCode('name')->willReturn(true);
        $productModel->getFamilyVariant()->willReturn(null);
        $productModel->getFamilyVariant()->willReturn(null);

        $propertyClearer->clear(
            $productModel,
            'name',
            ['locale' => null, 'scope' => null]
        )->shouldBeCalled();

        $this->applyAction($clearerAction, [$productModel])->shouldReturn([$productModel]);
    }

    function it_does_not_apply_action_if_entity_is_a_product_model_and_field_is_groups(
        PropertyClearerInterface $propertyClearer,
        EventDispatcherInterface $eventDispatcher,
        ProductModelInterface $productModel
    ) {
        $action = new ProductClearAction(['field' => 'groups']);

        $propertyClearer->clear(Argument::cetera())->shouldNotBeCalled();
        $eventDispatcher->dispatch(Argument::type(SkippedActionForSubjectEvent::class))->shouldBeCalled();

        $this->applyAction($action, [$productModel])->shouldReturn([]);
    }

    function it_does_not_apply_clear_attribute_action_on_product_model_when_variation_level_is_not_right(
        PropertyClearerInterface $propertyClearer,
        GetAttributes $getAttributes,
        EventDispatcherInterface $eventDispatcher,
        ProductModel $productModel,
        FamilyInterface $family,
        FamilyVariantInterface $familyVariant
    ) {
        $clearerAction = new ProductClearAction(['field' => 'name']);

        $attribute = $this->buildAttribute('name');
        $getAttributes->forCode('name')->willReturn($attribute);

        $productModel->getFamily()->willReturn($family);
        $family->hasAttributeCode('name')->willReturn(true);
        $productModel->getFamilyVariant()->willReturn(null);
        $productModel->getVariationLevel()->willReturn(1);
        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getLevelForAttributeCode('name')->willReturn(2);

        $propertyClearer->clear(Argument::cetera())->shouldNotBeCalled();
        $eventDispatcher->dispatch(Argument::type(SkippedActionForSubjectEvent::class))->shouldBeCalled();

        $this->applyAction($clearerAction, [$productModel])->shouldReturn([]);
    }

    function it_clears_an_attribute_value_even_if_attribute_does_not_belong_to_the_family(
        PropertyClearerInterface $propertyClearer,
        GetAttributes $getAttributes,
        Product $product,
        FamilyInterface $family
    ) {
        $clearerAction = new ProductClearAction(['field' => 'name']);

        $getAttributes->forCode('name')->willReturn($this->buildAttribute('name'));
        $product->getFamily()->willReturn($family);
        $family->hasAttributeCode('name')->willReturn(false);

        $propertyClearer->clear(
            $product,
            'name',
            ['locale' => null, 'scope' => null]
        )->shouldBeCalled();

        $this->applyAction($clearerAction, [$product])->shouldReturn([$product]);
    }

    function it_applies_clear_action_on_product_field(PropertyClearerInterface $propertyClearer)
    {
        $clearerAction = new ProductClearAction(['field' => 'categories']);
        $product = new Product();

        $propertyClearer->clear(
            $product,
            'categories',
            ['locale' => null, 'scope' => null]
        )->shouldBeCalled();

        $this->applyAction($clearerAction, [$product])->shouldReturn([$product]);
    }

    private function buildAttribute(string $code): Attribute
    {
        return new Attribute(
            $code,
            'type',
            [],
            false,
            false,
            null,
            null,
            true,
            '',
            []
        );
    }
}
