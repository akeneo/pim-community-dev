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

use Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\Concatenate\ValueStringifierInterface;
use Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\Concatenate\ValueStringifierRegistry;
use Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\ConcatenateActionApplier;
use Akeneo\Pim\Automation\RuleEngine\Component\Event\SkippedActionForSubjectEvent;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductAddActionInterface;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductConcatenateActionInterface;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductSourceCollection;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductTarget;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\Family;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute as ConnectorAttribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertySetterInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
class ConcatenateActionApplierSpec extends ObjectBehavior
{
    function let(
        PropertySetterInterface $propertySetter,
        ValueStringifierRegistry $valueStringifierRegistry,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        GetAttributes $getAttributes,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->beConstructedWith(
            $propertySetter,
            $valueStringifierRegistry,
            $attributeRepository,
            $getAttributes,
            $eventDispatcher
        );
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(ConcatenateActionApplier::class);
    }

    function it_supports_only_concatenate_action(
        ProductConcatenateActionInterface $productConcatenateAction,
        ProductAddActionInterface $productAddAction
    ) {
        $this->supports($productConcatenateAction)->shouldBe(true);
        $this->supports($productAddAction)->shouldBe(false);
    }

    function it_applies_a_concatenate_action_on_a_simple_product(
        PropertySetterInterface $propertySetter,
        ValueStringifierRegistry $valueStringifierRegistry,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        GetAttributes $getAttributes,
        ProductConcatenateActionInterface $concatenateAction,
        ValueStringifierInterface $valueStringifier1,
        ValueStringifierInterface $valueStringifier2
    ) {
        $productSourceCollection = ProductSourceCollection::fromNormalized([
            ['field' => 'model'],
            ['field' => 'title', 'scope' => 'ecommerce', 'locale' => 'en_US'],
            ['new_line' => null],
            ['text' => 'a text:'],
            ['field' => 'model'],
        ]);
        $concatenateAction->getSourceCollection()->willReturn($productSourceCollection);
        $concatenateAction->getTarget()->willReturn(ProductTarget::fromNormalized([
            'field' => 'description',
            'locale' => 'en_US',
            'scope' => 'print',
        ]));

        $family = new Family();
        $modelAttribute = new Attribute();
        $modelAttribute->setCode('model');
        $modelAttribute->setType('type1');
        $family->addAttribute($modelAttribute);
        $titleAttribute = new Attribute();
        $titleAttribute->setCode('title');
        $titleAttribute->setType('type2');
        $family->addAttribute($titleAttribute);
        $descriptionAttribute = new Attribute();
        $descriptionAttribute->setCode('description');
        $descriptionAttribute->setType('text');
        $family->addAttribute($descriptionAttribute);

        $value1 = ScalarValue::value('model', 'model_value');
        $value2 = ScalarValue::scopableLocalizableValue('title', 'title_value', 'ecommerce', 'en_US');
        $product = new Product();
        $product->setvalues(new WriteValueCollection([$value1, $value2]));
        $product->setFamily($family);

        $attributeRepository->findOneByIdentifier('description')
            ->willReturn($this->buildAttribute('description', AttributeTypes::TEXTAREA, false));
        $getAttributes->forCode('description')->willReturn($this->buildConnectorAttribute('description', AttributeTypes::TEXTAREA));

        $getAttributes->forCode('model')->willReturn($this->buildConnectorAttribute('model', 'type1'));
        $valueStringifierRegistry->getStringifier('type1')->willReturn($valueStringifier1);
        $valueStringifier1->stringify($value1, ['target_attribute_code' => 'description'])->willReturn('model_value');

        $getAttributes->forCode('title')->willReturn($this->buildConnectorAttribute('title', 'type2'));
        $valueStringifierRegistry->getStringifier('type2')->willReturn($valueStringifier2);
        $valueStringifier2->stringify($value2, ['target_attribute_code' => 'description'])->willReturn('title_value');

        $propertySetter->setData(
            $product,
            'description',
            "model_value title_value\na text:model_value",
            ['locale' => 'en_US', 'scope' => 'print']
        )->shouldBeCalled();

        $this->applyAction($concatenateAction, [$product])->shouldReturn([$product]);
    }

    function it_applies_a_concatenate_action_on_product_model_with_wysiwig_target(
        PropertySetterInterface $propertySetter,
        ValueStringifierRegistry $valueStringifierRegistry,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        GetAttributes $getAttributes,
        ProductConcatenateActionInterface $concatenateAction,
        ProductModelInterface $productModel,
        FamilyInterface $family,
        FamilyVariantInterface $familyVariant,
        ValueStringifierInterface $valueStringifier1,
        ValueStringifierInterface $valueStringifier2,
        ValueInterface $value1,
        ValueInterface $value2
    ) {
        $productSourceCollection = ProductSourceCollection::fromNormalized([
            ['field' => 'model'],
            ['field' => 'title', 'scope' => 'ecommerce', 'locale' => 'en_US'],
            ['new_line' => null],
            ['text' => 'a text:'],
            ['field' => 'model'],
        ]);
        $concatenateAction->getSourceCollection()->willReturn($productSourceCollection);
        $concatenateAction->getTarget()->willReturn(ProductTarget::fromNormalized([
            'field' => 'description',
            'locale' => 'en_US',
            'scope' => 'print',
        ]));

        $productModel->getFamily()->willReturn($family);
        $family->hasAttributeCode('description')->willReturn(true);
        $productModel->getFamilyVariant()->willReturn(null);
        $productModel->getVariationLevel()->willReturn(1);
        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getLevelForAttributeCode('description')->willReturn(1);

        $attributeRepository->findOneByIdentifier('description')
            ->willReturn($this->buildAttribute('description', AttributeTypes::TEXTAREA, true));
        $getAttributes->forCode('description')->willReturn($this->buildConnectorAttribute('description', AttributeTypes::TEXTAREA));

        $productModel->getValue('model', null, null)->willReturn($value1);
        $getAttributes->forCode('model')->willReturn($this->buildConnectorAttribute('model', 'type1'));
        $valueStringifierRegistry->getStringifier('type1')->willReturn($valueStringifier1);
        $valueStringifier1->stringify($value1, ['target_attribute_code' => 'description'])->willReturn('model_value');

        $productModel->getValue('title', 'en_US', 'ecommerce')->willReturn($value2);
        $getAttributes->forCode('title')->willReturn($this->buildConnectorAttribute('title', 'type2'));
        $valueStringifierRegistry->getStringifier('type2')->willReturn($valueStringifier2);
        $valueStringifier2->stringify($value2, ['target_attribute_code' => 'description'])->willReturn('title_value');

        $propertySetter->setData(
            $productModel,
            'description',
            'model_value title_value<br/>a text:model_value',
            ['locale' => 'en_US', 'scope' => 'print']
        )->shouldBeCalled();

        $this->applyAction($concatenateAction, [$productModel])->shouldReturn([$productModel]);
    }

    function it_applies_a_concatenate_action_on_product_model_with_text_target(
        PropertySetterInterface $propertySetter,
        ValueStringifierRegistry $valueStringifierRegistry,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        GetAttributes $getAttributes,
        ProductConcatenateActionInterface $concatenateAction,
        ProductModelInterface $productModel,
        FamilyInterface $family,
        FamilyVariantInterface $familyVariant,
        ValueStringifierInterface $valueStringifier1,
        ValueStringifierInterface $valueStringifier2,
        ValueInterface $value1,
        ValueInterface $value2
    ) {
        $productSourceCollection = ProductSourceCollection::fromNormalized([
            ['field' => 'model'],
            ['field' => 'title', 'scope' => 'ecommerce', 'locale' => 'en_US'],
            ['new_line' => null],
            ['text' => '[a text]'],
            ['field' => 'model'],
        ]);
        $concatenateAction->getSourceCollection()->willReturn($productSourceCollection);
        $concatenateAction->getTarget()->willReturn(ProductTarget::fromNormalized([
            'field' => 'description',
            'locale' => 'en_US',
            'scope' => 'print',
        ]));

        $productModel->getFamily()->willReturn($family);
        $family->hasAttributeCode('description')->willReturn(true);
        $productModel->getFamilyVariant()->willReturn(null);
        $productModel->getVariationLevel()->willReturn(1);
        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getLevelForAttributeCode('description')->willReturn(1);

        $attributeRepository->findOneByIdentifier('description')
            ->willReturn($this->buildAttribute('description', AttributeTypes::TEXT, false));
        $getAttributes->forCode('description')->willReturn($this->buildConnectorAttribute('description', AttributeTypes::TEXT));

        $productModel->getValue('model', null, null)->willReturn($value1);
        $getAttributes->forCode('model')->willReturn($this->buildConnectorAttribute('model', 'type1'));
        $valueStringifierRegistry->getStringifier('type1')->willReturn($valueStringifier1);
        $valueStringifier1->stringify($value1, ['target_attribute_code' => 'description'])->willReturn('model_value');

        $productModel->getValue('title', 'en_US', 'ecommerce')->willReturn($value2);
        $getAttributes->forCode('title')->willReturn($this->buildConnectorAttribute('title', 'type2'));
        $valueStringifierRegistry->getStringifier('type2')->willReturn($valueStringifier2);
        $valueStringifier2->stringify($value2, ['target_attribute_code' => 'description'])->willReturn('title_value');

        $propertySetter->setData(
            $productModel,
            'description',
            'model_value title_value[a text]model_value',
            ['locale' => 'en_US', 'scope' => 'print']
        )->shouldBeCalled();

        $this->applyAction($concatenateAction, [$productModel])->shouldReturn([$productModel]);
    }

    function it_skips_the_entity_if_a_value_is_not_found(
        PropertySetterInterface $propertySetter,
        ValueStringifierRegistry $valueStringifierRegistry,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        GetAttributes $getAttributes,
        EventDispatcherInterface $eventDispatcher,
        ProductConcatenateActionInterface $concatenateAction,
        EntityWithFamilyVariantInterface $entity,
        ValueStringifierInterface $valueStringifier,
        ValueInterface $value,
        FamilyInterface $family
    ) {
        $productSourceCollection = ProductSourceCollection::fromNormalized([
            ['field' => 'model'],
            ['field' => 'title', 'scope' => 'ecommerce', 'locale' => 'en_US'],
        ]);
        $concatenateAction->getSourceCollection()->willReturn($productSourceCollection);
        $concatenateAction->getTarget()->willReturn(ProductTarget::fromNormalized([
            'field' => 'description',
            'locale' => 'en_US',
            'scope' => 'print',
        ]));

        $entity->getFamily()->willReturn($family);
        $family->hasAttributeCode('description')->willReturn(true);
        $entity->getFamilyVariant()->willReturn(null);

        $attributeRepository->findOneByIdentifier('description')
            ->willReturn($this->buildAttribute('description', AttributeTypes::TEXTAREA, true));
        $getAttributes->forCode('description')->willReturn($this->buildConnectorAttribute('description', AttributeTypes::TEXTAREA));

        $entity->getValue('model', null, null)->willReturn($value);
        $getAttributes->forCode('model')->willReturn($this->buildConnectorAttribute('model', 'type1'));
        $valueStringifierRegistry->getStringifier('type1')->willReturn($valueStringifier);
        $valueStringifier->stringify($value, ['target_attribute_code' => 'description'])->willReturn('model_value');

        $getAttributes->forCode('title')->willReturn($this->buildConnectorAttribute('title', 'type'));
        $entity->getValue('title', 'en_US', 'ecommerce')->willReturn(null);

        $propertySetter->setData(Argument::cetera())->shouldNotBeCalled();
        $eventDispatcher->dispatch(Argument::type(SkippedActionForSubjectEvent::class))->shouldBeCalled();

        $this->applyAction($concatenateAction, [$entity])->shouldReturn([]);;
    }

    function it_throws_an_exception_when_a_stringifier_is_not_found(
        PropertySetterInterface $propertySetter,
        ValueStringifierRegistry $valueStringifierRegistry,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        GetAttributes $getAttributes,
        ProductConcatenateActionInterface $concatenateAction,
        EntityWithFamilyVariantInterface $entity,
        ValueStringifierInterface $valueStringifier1,
        ValueInterface $value1,
        ValueInterface $value2,
        FamilyInterface $family
    ) {
        $productSourceCollection = ProductSourceCollection::fromNormalized([
            ['field' => 'model'],
            ['field' => 'title', 'scope' => 'ecommerce', 'locale' => 'en_US'],
        ]);
        $concatenateAction->getSourceCollection()->willReturn($productSourceCollection);
        $concatenateAction->getTarget()->willReturn(ProductTarget::fromNormalized([
            'field' => 'description',
            'locale' => 'en_US',
            'scope' => 'print',
        ]));

        $attributeRepository->findOneByIdentifier('description')
            ->willReturn($this->buildAttribute('description', AttributeTypes::TEXTAREA, true));
        $getAttributes->forCode('description')->willReturn($this->buildConnectorAttribute('description', AttributeTypes::TEXTAREA));

        $entity->getFamily()->willReturn($family);
        $family->hasAttributeCode('description')->willReturn(true);
        $entity->getFamilyVariant()->willReturn(null);

        $entity->getValue('model', null, null)->willReturn($value1);
        $getAttributes->forCode('model')->willReturn($this->buildConnectorAttribute('model', 'type1'));
        $valueStringifierRegistry->getStringifier('type1')->willReturn($valueStringifier1);
        $valueStringifier1->stringify($value1, ['target_attribute_code' => 'description'])->willReturn('model_value');

        $entity->getValue('title', 'en_US', 'ecommerce')->willReturn($value2);
        $getAttributes->forCode('title')->willReturn($this->buildConnectorAttribute('title', 'type2'));
        $valueStringifierRegistry->getStringifier('type2')->willReturn(null);

        $propertySetter->setData(Argument::cetera())->shouldNotBeCalled();

        $this->shouldThrow(\InvalidArgumentException::class)->during('applyAction', [$concatenateAction, [$entity]]);
    }

    function it_does_not_apply_concatenate_action_on_entity_with_family_variant_if_variation_level_is_not_right(
        PropertySetterInterface $propertySetter,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        GetAttributes $getAttributes,
        EventDispatcherInterface $eventDispatcher,
        ProductConcatenateActionInterface $concatenateAction,
        ProductModelInterface $productModel,
        FamilyInterface $family,
        FamilyVariantInterface $familyVariant
    ) {
        $productSourceCollection = ProductSourceCollection::fromNormalized([
            ['field' => 'model'],
            ['field' => 'title', 'scope' => 'ecommerce', 'locale' => 'en_US'],
        ]);
        $concatenateAction->getSourceCollection()->willReturn($productSourceCollection);
        $concatenateAction->getTarget()->willReturn(ProductTarget::fromNormalized([
            'field' => 'description',
            'locale' => 'en_US',
            'scope' => 'print',
        ]));

        $attributeRepository->findOneByIdentifier('description')
            ->willReturn($this->buildAttribute('description', AttributeTypes::TEXTAREA, true));
        $getAttributes->forCode('description')->willReturn($this->buildConnectorAttribute('description', AttributeTypes::TEXTAREA));

        $productModel->getFamily()->willReturn($family);
        $family->hasAttributeCode('description')->willReturn(true);
        $productModel->getFamilyVariant()->willReturn(null);
        $productModel->getVariationLevel()->willReturn(1);
        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getLevelForAttributeCode('description')->willReturn(2);

        $propertySetter->setData(Argument::cetera())->shouldNotBeCalled();
        $eventDispatcher->dispatch(Argument::type(SkippedActionForSubjectEvent::class))->shouldBeCalled();

        $this->applyAction($concatenateAction, [$productModel])->shouldReturn([]);
    }

    private function buildConnectorAttribute(string $code, string $type): ConnectorAttribute
    {
        return new ConnectorAttribute(
            $code,
            $type,
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

    private function buildAttribute(string $code, string $type, bool $isWysiwygEnabled): Attribute
    {
        $attribute = new Attribute();
        $attribute->setCode($code);
        $attribute->setType($type);
        $attribute->setWysiwygEnabled($isWysiwygEnabled);

        return $attribute;
    }
}
