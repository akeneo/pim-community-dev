<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\EventSubscriber\EntityWithValues;

use Akeneo\Channel\Component\Query\GetChannelCodeWithLocaleCodesInterface;
use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\EntityWithValues\AddDefaultValuesSubscriber;
use Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\CommonAttributeCollection;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Structure\Component\Model\VariantAttributeSetInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class AddDefaultValuesSubscriberSpec extends ObjectBehavior
{
    function let(
        GetAttributes $getAttributes,
        ValueFactory $valueFactory,
        GetChannelCodeWithLocaleCodesInterface $getChannelWithLocales
    ) {
        $getChannelWithLocales->findAll()->willReturn(
            [
                [
                    'channelCode' => 'ecommerce',
                    'localeCodes' => ['en_US', 'de_DE'],
                ],
                [
                    'channelCode' => 'mobile',
                    'localeCodes' => ['en_US', 'fr_FR'],
                ],
            ]
        );
        $this->beConstructedWith($getAttributes, $valueFactory, $getChannelWithLocales);
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AddDefaultValuesSubscriber::class);
    }

    function it_subscribes_to_pre_save_events()
    {
        $this::getSubscribedEvents()->shouldHaveKey(StorageEvents::PRE_SAVE);
    }

    function it_does_nothing_if_the_entity_is_not_an_entity_with_family_variant(ValueFactory $valueFactory)
    {
        $valueFactory->createByCheckingData(Argument::cetera())->shouldNotBeCalled();

        $this->addDefaultValues(
            new GenericEvent(new \stdClass(), ['is_new' => true])
        );
    }

    function it_does_nothing_if_the_entity_is_not_new(ValueFactory $valueFactory, EntityWithFamilyVariantInterface $product)
    {
        $valueFactory->createByCheckingData(Argument::cetera())->shouldNotBeCalled();
        $product->addValue(Argument::any())->shouldNotBeCalled();

        $this->addDefaultValues(
            new GenericEvent($product->getWrappedObject())
        );
    }

    function it_does_nothing_if_the_add_default_values_option_is_false(
        ValueFactory $valueFactory,
        EntityWithFamilyVariantInterface $entity
    ) {
        $valueFactory->createByCheckingData(Argument::cetera())->shouldNotBeCalled();
        $entity->addValue(Argument::any())->shouldNotBeCalled();

        $this->addDefaultValues(
            new GenericEvent($entity->getWrappedObject(), ['is_new' => true, 'add_default_values' => false])
        );
    }

    function it_does_nothing_if_the_entity_has_no_family(
        ValueFactory $valueFactory,
        EntityWithFamilyVariantInterface $entity
    ) {
        $entity->getFamilyVariant()->willReturn(null);
        $entity->getFamily()->willReturn(null);

        $valueFactory->createByCheckingData(Argument::cetera())->shouldNotBeCalled();
        $entity->addValue(Argument::any())->shouldNotBeCalled();

        $this->addDefaultValues(
            new GenericEvent($entity->getWrappedObject(), ['is_new' => true])
        );
    }

    function it_does_nothing_if_the_entity_has_no_attributes_with_default_values(
        ValueFactory $valueFactory,
        EntityWithFamilyVariantInterface $entity,
        FamilyInterface $family,
        AttributeInterface $sku,
        AttributeInterface $name
    ) {
        $sku->getProperty('default_value')->willReturn(null);
        $name->getProperty('default_value')->willReturn(null);
        $family->getAttributes()->willReturn(
            new ArrayCollection([$sku->getWrappedObject(), $name->getWrappedObject()])
        );
        $entity->getFamilyVariant()->willReturn(null);
        $entity->getFamily()->willReturn($family);

        $valueFactory->createByCheckingData(Argument::cetera())->shouldNotBeCalled();
        $entity->addValue(Argument::any())->shouldNotBeCalled();

        $this->addDefaultValues(
            new GenericEvent($entity->getWrappedObject(), ['is_new' => true])
        );
    }

    function it_adds_default_values_to_a_simple_product(
        GetAttributes $getAttributes,
        ValueFactory $valueFactory,
        EntityWithFamilyVariantInterface $entity,
        ValueInterface $value,
        FamilyInterface $family,
        AttributeInterface $autofocus
    ) {
        $autofocus->getCode()->willReturn('autofocus');
        $autofocus->getProperty('default_value')->willReturn(true);
        $family->getAttributes()->willReturn(
            new ArrayCollection([$autofocus->getWrappedObject()])
        );
        $entity->getId()->willReturn(null);
        $entity->getFamilyVariant()->willReturn(null);
        $entity->getFamily()->willReturn($family);

        $attribute = $this->createAttributeWithDefaultValue('autofocus', true);
        $getAttributes->forCodes(['autofocus'])->shouldBeCalled()->willReturn(['autofocus' => $attribute]);
        $valueFactory->createByCheckingData($attribute, null, null, true)->shouldBeCalled()->willReturn($value);
        $entity->addValue($value)->shouldBeCalled();

        $this->addDefaultValues(
            new GenericEvent($entity->getWrappedObject(), ['is_new' => true])
        );
    }

    function it_adds_default_values_to_a_variant_product(
        GetAttributes $getAttributes,
        ValueFactory $valueFactory,
        ProductModelInterface $product,
        ValueInterface $value,
        VariantAttributeSetInterface $variantAttributeSet,
        FamilyVariantInterface $familyVariant,
        AttributeInterface $autofocus
    ) {
        $autofocus->getCode()->willReturn('autofocus');
        $autofocus->getProperty('default_value')->willReturn(true);
        $variantAttributeSet->getAttributes()->willReturn(new ArrayCollection([$autofocus->getWrappedObject()]));
        $familyVariant->getVariantAttributeSet(2)->willReturn($variantAttributeSet);
        $product->getFamilyVariant()->willReturn($familyVariant);
        $product->getVariationLevel()->willReturn(2);
        $product->getId()->willReturn(null);

        $attribute = $this->createAttributeWithDefaultValue('autofocus', true);
        $getAttributes->forCodes(['autofocus'])->shouldBeCalled()->willReturn(['autofocus' => $attribute]);
        $valueFactory->createByCheckingData($attribute, null, null, true)->shouldBeCalled()->willReturn($value);
        $product->addValue($value)->shouldBeCalled();

        $this->addDefaultValues(
            new GenericEvent($product->getWrappedObject(), ['is_new' => true])
        );
    }

    function it_adds_default_values_to_a_root_product_model(
        GetAttributes $getAttributes,
        ValueFactory $valueFactory,
        ProductModelInterface $productModel,
        ValueInterface $value,
        FamilyVariantInterface $familyVariant,
        AttributeInterface $autofocus
    ) {
        $autofocus->getCode()->willReturn('autofocus');
        $autofocus->getProperty('default_value')->willReturn(true);
        $familyVariant->getCommonAttributes()->willReturn(
            new CommonAttributeCollection([$autofocus->getWrappedObject()])
        );
        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $productModel->getVariationLevel()->willReturn(0);
        $productModel->getId()->willReturn(null);

        $attribute = $this->createAttributeWithDefaultValue('autofocus', true);
        $getAttributes->forCodes(['autofocus'])->shouldBeCalled()->willReturn(['autofocus' => $attribute]);
        $valueFactory->createByCheckingData($attribute, null, null, true)->shouldBeCalled()->willReturn($value);
        $productModel->addValue($value)->shouldBeCalled();

        $this->addDefaultValues(
            new GenericEvent($productModel->getWrappedObject(), ['is_new' => true])
        );
    }

    function it_adds_default_values_for_a_scopable_attribute(
        GetAttributes $getAttributes,
        ValueFactory $valueFactory,
        EntityWithFamilyVariantInterface $entity,
        ValueInterface $ecommerceValue,
        ValueInterface $mobileValue,
        FamilyInterface $family,
        AttributeInterface $autofocus
    ) {
        $autofocus->getCode()->willReturn('autofocus');
        $autofocus->getProperty('default_value')->willReturn(true);
        $family->getAttributes()->willReturn(
            new ArrayCollection([$autofocus->getWrappedObject()])
        );
        $entity->getId()->willReturn(null);
        $entity->getFamilyVariant()->willReturn(null);
        $entity->getFamily()->willReturn($family);

        $attribute = $this->createAttributeWithDefaultValue('autofocus', true, false, true);
        $getAttributes->forCodes(['autofocus'])->shouldBeCalled()->willReturn(['autofocus' => $attribute]);

        $valueFactory->createByCheckingData($attribute, 'ecommerce', null, true)
                     ->shouldBeCalled()->willReturn($ecommerceValue);
        $entity->addValue($ecommerceValue)->shouldBeCalled();
        $valueFactory->createByCheckingData($attribute, 'mobile', null, true)
                     ->shouldBeCalled()->willReturn($mobileValue);
        $entity->addValue($mobileValue)->shouldBeCalled();

        $this->addDefaultValues(
            new GenericEvent($entity->getWrappedObject(), ['is_new' => true])
        );
    }

    function it_adds_default_values_for_a_localizable_attribute(
        GetAttributes $getAttributes,
        ValueFactory $valueFactory,
        EntityWithFamilyVariantInterface $entity,
        ValueInterface $enUSValue,
        ValueInterface $frFRValue,
        ValueInterface $deDEValue,
        FamilyInterface $family,
        AttributeInterface $autofocus
    ) {
        $autofocus->getCode()->willReturn('autofocus');
        $autofocus->getProperty('default_value')->willReturn(true);
        $family->getAttributes()->willReturn(
            new ArrayCollection([$autofocus->getWrappedObject()])
        );
        $entity->getId()->willReturn(null);
        $entity->getFamilyVariant()->willReturn(null);
        $entity->getFamily()->willReturn($family);

        $attribute = $this->createAttributeWithDefaultValue('autofocus', true, true);
        $getAttributes->forCodes(['autofocus'])->shouldBeCalled()->willReturn(['autofocus' => $attribute]);

        $valueFactory->createByCheckingData($attribute, null, 'en_US', true)
                     ->shouldBeCalled()->willReturn($enUSValue);
        $entity->addValue($enUSValue)->shouldBeCalled();
        $valueFactory->createByCheckingData($attribute, null, 'de_DE', true)
                     ->shouldBeCalled()->willReturn($deDEValue);
        $entity->addValue($deDEValue)->shouldBeCalled();
        $valueFactory->createByCheckingData($attribute, null, 'fr_FR', true)
                     ->shouldBeCalled()->willReturn($frFRValue);
        $entity->addValue($frFRValue)->shouldBeCalled();

        $this->addDefaultValues(
            new GenericEvent($entity->getWrappedObject(), ['is_new' => true])
        );
    }

    function it_adds_default_values_for_a_scopable_and_localizable_attribute(
        GetAttributes $getAttributes,
        ValueFactory $valueFactory,
        EntityWithFamilyVariantInterface $entity,
        ValueInterface $ecommerceEnUSValue,
        ValueInterface $ecommerceDeDEValue,
        ValueInterface $mobileEnUSalue,
        ValueInterface $mobileFrFRValue,
        FamilyInterface $family,
        AttributeInterface $autofocus
    ) {
        $autofocus->getCode()->willReturn('autofocus');
        $autofocus->getProperty('default_value')->willReturn(true);
        $family->getAttributes()->willReturn(
            new ArrayCollection([$autofocus->getWrappedObject()])
        );
        $entity->getId()->willReturn(null);
        $entity->getFamilyVariant()->willReturn(null);
        $entity->getFamily()->willReturn($family);

        $attribute = $this->createAttributeWithDefaultValue('autofocus', true, true, true);
        $getAttributes->forCodes(['autofocus'])->shouldBeCalled()->willReturn(['autofocus' => $attribute]);

        $valueFactory->createByCheckingData($attribute, 'ecommerce', 'en_US', true)->shouldBeCalled()->willReturn(
            $ecommerceEnUSValue
        );
        $entity->addValue($ecommerceEnUSValue)->shouldBeCalled();
        $valueFactory->createByCheckingData($attribute, 'ecommerce', 'de_DE', true)
                     ->shouldBeCalled()->willReturn($ecommerceDeDEValue);
        $entity->addValue($ecommerceDeDEValue)->shouldBeCalled();
        $valueFactory->createByCheckingData($attribute, 'mobile', 'en_US', true)
                     ->shouldBeCalled()->willReturn($mobileEnUSalue);
        $entity->addValue($mobileEnUSalue)->shouldBeCalled();
        $valueFactory->createByCheckingData($attribute, 'mobile', 'fr_FR', true)
                     ->shouldBeCalled()->willReturn($mobileFrFRValue);
        $entity->addValue($mobileFrFRValue)->shouldBeCalled();

        $this->addDefaultValues(
            new GenericEvent($entity->getWrappedObject(), ['is_new' => true])
        );
    }

    function it_adds_default_values_for_a_locale_specific_attribute(
        GetAttributes $getAttributes,
        ValueFactory $valueFactory,
        EntityWithFamilyVariantInterface $entity,
        ValueInterface $ecommerceEnUSValue,
        ValueInterface $mobileEnUSalue,
        ValueInterface $mobileFrFRValue,
        FamilyInterface $family,
        AttributeInterface $autofocus
    ) {
        $autofocus->getCode()->willReturn('autofocus');
        $autofocus->getProperty('default_value')->willReturn(true);
        $family->getAttributes()->willReturn(
            new ArrayCollection([$autofocus->getWrappedObject()])
        );
        $entity->getId()->willReturn(null);
        $entity->getFamilyVariant()->willReturn(null);
        $entity->getFamily()->willReturn($family);

        $attribute = $this->createAttributeWithDefaultValue('autofocus', true, true, true, ['en_US', 'fr_FR']);
        $getAttributes->forCodes(['autofocus'])->shouldBeCalled()->willReturn(['autofocus' => $attribute]);

        $valueFactory->createByCheckingData($attribute, 'ecommerce', 'en_US', true)
                     ->shouldBeCalled()->willReturn($ecommerceEnUSValue);
        $entity->addValue($ecommerceEnUSValue)->shouldBeCalled();
        $valueFactory->createByCheckingData($attribute, 'mobile', 'en_US', true)
                     ->shouldBeCalled()->willReturn($mobileEnUSalue);
        $entity->addValue($mobileEnUSalue)->shouldBeCalled();
        $valueFactory->createByCheckingData($attribute, 'mobile', 'fr_FR', true)
                     ->shouldBeCalled()->willReturn($mobileFrFRValue);
        $entity->addValue($mobileFrFRValue)->shouldBeCalled();

        $valueFactory->createByCheckingData($attribute, 'ecommerce', 'de_DE', true)->shouldNotBeCalled();

        $this->addDefaultValues(
            new GenericEvent($entity->getWrappedObject(), ['is_new' => true])
        );
    }

    function it_adds_values_for_several_attributes_with_default_values(
        GetAttributes $getAttributes,
        ValueFactory $valueFactory,
        EntityWithFamilyVariantInterface $entity,
        ValueInterface $autoFocusValue,
        ValueInterface $colorScanningEcommerceValue,
        ValueInterface $colorScanningMobileValue,
        FamilyInterface $family,
        AttributeInterface $autofocus,
        AttributeInterface $colorScanning
    ) {
        $autofocus->getCode()->willReturn('autofocus');
        $autofocus->getProperty('default_value')->willReturn(true);
        $colorScanning->getCode()->willReturn('color_scanning');
        $colorScanning->getProperty('default_value')->willReturn(false);

        $family->getAttributes()->willReturn(
            new ArrayCollection([$autofocus->getWrappedObject(), $colorScanning->getWrappedObject()])
        );
        $entity->getId()->willReturn(null);
        $entity->getFamilyVariant()->willReturn(null);
        $entity->getFamily()->willReturn($family);

        $readAutofocus = $this->createAttributeWithDefaultValue('autofocus', true);
        $readColorScanning = $this->createAttributeWithDefaultValue('color_scanning', false, false, true);
        $getAttributes->forCodes(['autofocus', 'color_scanning'])->shouldBeCalled()->willReturn(
            [
                'autofocus' => $readAutofocus,
                'color_scanning' => $readColorScanning,
            ]
        );

        $valueFactory->createByCheckingData($readAutofocus, null, null, true)
                     ->shouldBeCalled()->willReturn($autoFocusValue);
        $entity->addValue($autoFocusValue)->shouldBeCalled();
        $valueFactory->createByCheckingData($readColorScanning, 'ecommerce', null, false)
                     ->shouldBeCalled()->willReturn($colorScanningEcommerceValue);
        $entity->addValue($colorScanningEcommerceValue)->shouldBeCalled();
        $valueFactory->createByCheckingData($readColorScanning, 'mobile', null, false)
                     ->shouldBeCalled()->willReturn($colorScanningMobileValue);
        $entity->addValue($colorScanningMobileValue)->shouldBeCalled();

        $this->addDefaultValues(
            new GenericEvent($entity->getWrappedObject(), ['is_new' => true])
        );
    }

    private function createAttributeWithDefaultValue(
        string $code,
        bool $defaultValue,
        bool $isLocalizable = false,
        bool $isScopable = false,
        array $availableLocaleCodes = []
    ): Attribute {
        return new Attribute(
            $code,
            'pim_catalog_boolean',
            ['default_value' => $defaultValue],
            $isLocalizable,
            $isScopable,
            null,
            null,
            null,
            'bool',
            $availableLocaleCodes
        );
    }
}
