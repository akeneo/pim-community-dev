<?php

namespace spec\Pim\Bundle\EnrichBundle\MassEditAction\Operation;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Bundle\CatalogBundle\Entity\AttributeRequirement;
use Pim\Bundle\CatalogBundle\Entity\Repository\ChannelRepository;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Factory\AttributeRequirementFactory;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;

class SetAttributeRequirementsSpec extends ObjectBehavior
{
    function let(
        ChannelRepository $channelRepository,
        AttributeRepository $attributeRepository,
        AttributeRequirementFactory $factory
    ) {
        $this->beConstructedWith($channelRepository, $attributeRepository, $factory);
    }

    function it_is_a_mass_edit_operation()
    {
        $this->shouldHaveType('Pim\Bundle\EnrichBundle\MassEditAction\Operation\MassEditOperationInterface');
    }

    function it_has_attribute_requirements(AttributeRequirement $requirement)
    {
        $requirement->getAttributeCode()->willReturn('foo');
        $requirement->getChannelCode()->willReturn('bar');
        $this->addAttributeRequirement($requirement);

        $this->getAttributeRequirements()->toArray()->shouldReturn([
            'foo_bar' => $requirement,
        ]);
    }

    function it_removes_its_own_attribute_requirements(AttributeRequirement $requirement)
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
        ChannelRepository $channelRepository,
        Channel $ecommerce,
        Channel $mobile,
        AttributeRepository $attributeRepository,
        AbstractAttribute $name,
        AbstractAttribute $description,
        AttributeRequirementFactory $factory,
        AttributeRequirement $r1,
        AttributeRequirement $r2,
        AttributeRequirement $r3,
        AttributeRequirement $r4
    ) {
        $channelRepository->findAll()->willReturn([
            $ecommerce, $mobile
        ]);
        $attributeRepository->getNonIdentifierAttributes()->willReturn([
            $name, $description
        ]);


        $factory->createAttributeRequirement($name, $ecommerce, false)->willReturn($r1);
        $r1->getAttributeCode()->willReturn('name');
        $r1->getChannelCode()->willReturn('ecommerce');

        $factory->createAttributeRequirement($name, $mobile, false)->willReturn($r2);
        $r2->getAttributeCode()->willReturn('name');
        $r2->getChannelCode()->willReturn('mobile');

        $factory->createAttributeRequirement($description, $ecommerce, false)->willReturn($r3);
        $r3->getAttributeCode()->willReturn('description');
        $r3->getChannelCode()->willReturn('ecommerce');

        $factory->createAttributeRequirement($description, $mobile, false)->willReturn($r4);
        $r4->getAttributeCode()->willReturn('description');
        $r4->getChannelCode()->willReturn('mobile');

        $this->initialize();

        $this->getAttributeRequirements()->toArray()->shouldReturn([
            'name_ecommerce' => $r1,
            'name_mobile' => $r2,
            'description_ecommerce' => $r3,
            'description_mobile' => $r4,
        ]);
    }
}
