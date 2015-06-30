<?php

namespace spec\Pim\Bundle\CatalogBundle\Builder;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\FamilyTranslation;
use Pim\Bundle\CatalogBundle\Factory\AttributeRequirementFactory;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeRequirementInterface;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\FamilyInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ChannelRepositoryInterface;
use Prophecy\Argument;

class FamilyBuilderSpec extends ObjectBehavior
{
    function let(
        ChannelRepositoryInterface $channelRepository,
        AttributeRequirementFactory $attRequiFactory,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->beConstructedWith($channelRepository, $attRequiFactory, $attributeRepository);
    }

    function it_creates_a_family_with_identifier(
        $attributeRepository,
        $channelRepository,
        $attRequiFactory,
        AttributeInterface $identifierAttribute,
        ChannelInterface $printChannel,
        ChannelInterface $ecommerceChannel,
        AttributeRequirementInterface $printRequirement,
        AttributeRequirementInterface $ecommerceRequirement
    ) {
        $attributeRepository->getIdentifier()
            ->willReturn($identifierAttribute)
            ->shouldBeCalled();

        $printRequirement->setFamily(Argument::any())
            ->willReturn(null);
        $printRequirement->getAttributeCode()
            ->willReturn('anyCode');
        $printRequirement->getChannelCode()
            ->willReturn('print');

        $ecommerceRequirement->setFamily(Argument::any())
            ->willReturn(null);
        $ecommerceRequirement->getAttributeCode()
            ->willReturn('anyCode');
        $ecommerceRequirement->getChannelCode()
            ->willReturn('ecommerce');

        $channelRepository->findAll()
            ->willReturn([$printChannel, $ecommerceChannel])
            ->shouldBeCalled();

        $attRequiFactory->createAttributeRequirement($identifierAttribute, $printChannel, true)
            ->willReturn($printRequirement)
            ->shouldBeCalled();

        $attRequiFactory->createAttributeRequirement($identifierAttribute, $ecommerceChannel, true)
            ->willReturn($ecommerceRequirement)
            ->shouldBeCalled();

        $family = $this->createFamily(true);
        $family->shouldBeAnInstanceOf('Pim\Bundle\CatalogBundle\Model\FamilyInterface');
        $family->getAttributes()->shouldHaveCount(1);
        $family->getAttributes()->first()->shouldBeEqualTo($identifierAttribute);
        $family->getAttributeRequirements()->shouldHaveCount(2);
        $family->getAttributeRequirements()->shouldBeEqualTo([
            'anyCode_print'     => $printRequirement,
            'anyCode_ecommerce' => $ecommerceRequirement
        ]);
    }

    function it_creates_a_family_without_identifier()
    {
        $family = $this->createFamily();
        $family->shouldBeAnInstanceOf('Pim\Bundle\CatalogBundle\Model\FamilyInterface');
    }

    function it_sets_labels(FamilyInterface $family, FamilyTranslation $translation)
    {
        $data = ['en_US' => 'label en us', 'fr_FR' => 'label fr fr'];
        $family->setLocale('en_US')->shouldBeCalled();
        $family->setLocale('fr_FR')->shouldBeCalled();
        $family->getTranslation()->willReturn($translation);

        $translation->setLabel('label en us');
        $translation->setLabel('label fr fr');
        $this->setLabels($family, $data);
    }

    function it_throws_an_exception_if_attribute_not_found(
        $attributeRepository,
        FamilyInterface $family
    ) {
        $data = ['mobile' => ['sku', 'name', 'size']];

        $attributeRepository->findOneByIdentifier('sku')->willReturn(null);

        $this->shouldThrow(new \InvalidArgumentException(sprintf('Attribute with "%s" code does not exist', 'sku')))
            ->during('setAttributeRequirements', [$family, $data]);
    }

    function it_throws_an_exception_if_channel_not_found(
        $channelRepository,
        $attributeRepository,
        AttributeInterface $attribute,
        FamilyInterface $family
    ) {
        $data = ['mobile' => ['sku', 'name']];

        $attributeRepository->findOneByIdentifier('sku')->willReturn($attribute);
        $channelRepository->findOneByIdentifier('mobile')->willReturn(null);

        $this->shouldThrow(new \InvalidArgumentException(sprintf('Channel with "%s" code does not exist', 'mobile')))
            ->during('setAttributeRequirements', [$family, $data]);
    }

    function it_sets_attribute_requirements(
        $channelRepository,
        $attributeRepository,
        $attRequiFactory,
        AttributeInterface $attribute1,
        AttributeInterface $attribute2,
        AttributeInterface $attribute3,
        AttributeRequirementInterface $attributeRequirement1,
        AttributeRequirementInterface $attributeRequirement2,
        AttributeRequirementInterface $attributeRequirement3,
        AttributeRequirementInterface $attributeRequirement4,
        ChannelInterface $channel1,
        ChannelInterface $channel2,
        FamilyInterface $family
    ) {
        $data = ['mobile' => ['sku', 'name', 'size'], 'print' => ['sku']];

        $attributeRepository->findOneByIdentifier('sku')->willReturn($attribute1);
        $attributeRepository->findOneByIdentifier('name')->willReturn($attribute2);
        $attributeRepository->findOneByIdentifier('size')->willReturn($attribute3);
        $channelRepository->findOneByIdentifier('mobile')->willReturn($channel1);
        $channelRepository->findOneByIdentifier('print')->willReturn($channel2);

        $attRequiFactory->createAttributeRequirement($attribute1, $channel1, true)->willReturn($attributeRequirement1);
        $attRequiFactory->createAttributeRequirement($attribute2, $channel1, true)->willReturn($attributeRequirement2);
        $attRequiFactory->createAttributeRequirement($attribute3, $channel1, true)->willReturn($attributeRequirement3);
        $attRequiFactory->createAttributeRequirement($attribute1, $channel2, true)->willReturn($attributeRequirement4);

        $family
            ->setAttributeRequirements(
                [$attributeRequirement1, $attributeRequirement2, $attributeRequirement3, $attributeRequirement4]
            )
            ->shouldBeCalled();

        $this->setAttributeRequirements($family, $data);
    }

    public function it_throws_an_exception_if_attribute_does_not_exist(FamilyInterface $family, $attributeRepository)
    {
        $data = ['sku'];

        $attributeRepository->findOneByIdentifier('sku')->willReturn(null);

        $this->shouldThrow(new \InvalidArgumentException(sprintf('Attribute with "%s" code does not exist', 'sku')))
            ->during('addAttributes', [$family, $data]);
    }

    public function it_adds_attributes(FamilyInterface $family, $attributeRepository, AttributeInterface $attribute)
    {
        $data = ['sku'];

        $attributeRepository->findOneByIdentifier('sku')->willReturn($attribute);
        $family->addAttribute($attribute)->shouldBeCalled();

        $this->addAttributes($family, $data);
    }
}
