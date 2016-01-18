<?php

namespace spec\Pim\Bundle\EnrichBundle\MassEditAction\Operation;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Factory\AttributeRequirementFactory;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeRequirementInterface;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ChannelRepositoryInterface;

class SetAttributeRequirementsSpec extends ObjectBehavior
{
    function let(
        ChannelRepositoryInterface $channelRepository,
        AttributeRepositoryInterface $attributeRepository,
        AttributeRequirementFactory $factory
    ) {
        $this->beConstructedWith($channelRepository, $attributeRepository, $factory);
    }

    function it_is_a_mass_edit_operation()
    {
        $this->shouldHaveType('Pim\Bundle\EnrichBundle\MassEditAction\Operation\MassEditOperationInterface');
    }

    function it_has_attribute_requirements(AttributeRequirementInterface $requirement)
    {
        $requirement->getAttributeCode()->willReturn('foo');
        $requirement->getChannelCode()->willReturn('bar');
        $this->addAttributeRequirement($requirement);

        $this->getAttributeRequirements()->toArray()->shouldReturn([
            'foo_bar' => $requirement,
        ]);
    }

    function it_removes_its_own_attribute_requirements(AttributeRequirementInterface $requirement)
    {
        $this->addAttributeRequirement($requirement);
        $this->removeAttributeRequirement($requirement);

        $this->getAttributeRequirements()->toArray()->shouldHaveCount(0);
    }

    function it_uses_the_set_attribute_requirements_form_type()
    {
        $this->getFormType()->shouldReturn('pim_enrich_mass_set_attribute_requirements');
    }

    function it_initializes_attribute_requirements_with_all_channels_and_attributes_in_the_PIM(
        ChannelRepositoryInterface $channelRepository,
        ChannelInterface $ecommerceChannel,
        ChannelInterface $mobileChannel,
        AttributeRepositoryInterface $attributeRepository,
        AttributeInterface $nameAttribute,
        AttributeInterface $descriptionAttribute,
        AttributeRequirementFactory $factory,
        AttributeRequirementInterface $nameECommerceRequirement,
        AttributeRequirementInterface $nameMobileRequirement,
        AttributeRequirementInterface $descriptionECommerceRequirement,
        AttributeRequirementInterface $descriptionMobileRequirement
    ) {
        $channelRepository->findAll()->willReturn([
            $ecommerceChannel, $mobileChannel
        ]);
        $attributeRepository->getNonIdentifierAttributes()->willReturn([
            $nameAttribute, $descriptionAttribute
        ]);

        $factory->createAttributeRequirement($nameAttribute, $ecommerceChannel, false)->willReturn($nameECommerceRequirement);
        $nameECommerceRequirement->getAttributeCode()->willReturn('name');
        $nameECommerceRequirement->getChannelCode()->willReturn('ecommerce');

        $factory->createAttributeRequirement($nameAttribute, $mobileChannel, false)->willReturn($nameMobileRequirement);
        $nameMobileRequirement->getAttributeCode()->willReturn('name');
        $nameMobileRequirement->getChannelCode()->willReturn('mobile');

        $factory->createAttributeRequirement($descriptionAttribute, $ecommerceChannel, false)->willReturn($descriptionECommerceRequirement);
        $descriptionECommerceRequirement->getAttributeCode()->willReturn('description');
        $descriptionECommerceRequirement->getChannelCode()->willReturn('ecommerce');

        $factory->createAttributeRequirement($descriptionAttribute, $mobileChannel, false)->willReturn($descriptionMobileRequirement);
        $descriptionMobileRequirement->getAttributeCode()->willReturn('description');
        $descriptionMobileRequirement->getChannelCode()->willReturn('mobile');

        $this->initialize();

        $this->getAttributeRequirements()->toArray()->shouldReturn([
            'name_ecommerce'        => $nameECommerceRequirement,
            'name_mobile'           => $nameMobileRequirement,
            'description_ecommerce' => $descriptionECommerceRequirement,
            'description_mobile'    => $descriptionMobileRequirement,
        ]);
    }

    function it_returns_well_formatted_actions_for_batch_job(
        AttributeInterface $attrColor,
        AttributeInterface $attrSize,
        ChannelInterface $channelMobile,
        ChannelInterface $channelEcommerce,
        AttributeRequirementInterface $colorMobileRequirement,
        AttributeRequirementInterface $colorEcommerceRequirement,
        AttributeRequirementInterface $sizeEcommerceRequirement
    ) {
        $attrColor->getCode()->willReturn('color');
        $attrSize->getCode()->willReturn('size');

        $channelMobile->getCode()->willReturn('mobile');
        $channelEcommerce->getCode()->willReturn('ecommerce');

        $colorMobileRequirement->getAttribute()->willReturn($attrColor);
        $colorEcommerceRequirement->getAttribute()->willReturn($attrColor);
        $sizeEcommerceRequirement->getAttribute()->willReturn($attrSize);

        $colorMobileRequirement->getChannel()->willReturn($channelMobile);
        $colorEcommerceRequirement->getChannel()->willReturn($channelEcommerce);
        $sizeEcommerceRequirement->getChannel()->willReturn($channelEcommerce);

        $colorMobileRequirement->isRequired()->willReturn(false);
        $colorEcommerceRequirement->isRequired()->willReturn(true);
        $sizeEcommerceRequirement->isRequired()->willReturn(true);

        $colorMobileRequirement->getAttributeCode()->willReturn('color');
        $colorEcommerceRequirement->getAttributeCode()->willReturn('color');
        $sizeEcommerceRequirement->getAttributeCode()->willReturn('size');

        $colorMobileRequirement->getChannelCode()->willReturn('mobile');
        $colorEcommerceRequirement->getChannelCode()->willReturn('ecommerce');
        $sizeEcommerceRequirement->getChannelCode()->willReturn('ecommerce');

        $this->addAttributeRequirement($colorMobileRequirement);
        $this->addAttributeRequirement($colorEcommerceRequirement);
        $this->addAttributeRequirement($sizeEcommerceRequirement);

        $this->getActions()->shouldReturn([
            [
                'attribute_code' => 'color',
                'channel_code' => 'mobile',
                'is_required' => false
            ],
            [
                'attribute_code' => 'color',
                'channel_code' => 'ecommerce',
                'is_required' => true
            ],
            [
                'attribute_code' => 'size',
                'channel_code' => 'ecommerce',
                'is_required' => true
            ]
        ]);
    }
}
