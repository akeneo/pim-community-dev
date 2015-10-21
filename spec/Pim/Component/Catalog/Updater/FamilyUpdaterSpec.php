<?php

namespace spec\Pim\Component\Catalog\Updater;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypes;
use Pim\Bundle\CatalogBundle\Entity\FamilyTranslation;
use Pim\Bundle\CatalogBundle\Factory\AttributeRequirementFactory;
use Pim\Bundle\CatalogBundle\Factory\FamilyFactory;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeRequirementInterface;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRequirementRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ChannelRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\FamilyRepositoryInterface;
use Prophecy\Argument;

class FamilyUpdaterSpec extends ObjectBehavior
{
    function let(
        FamilyRepositoryInterface $familyRepository,
        FamilyFactory $familyFactory,
        AttributeRepositoryInterface $attributeRepository,
        ChannelRepositoryInterface $channelRepository,
        AttributeRequirementFactory $attrRequiFactory,
        AttributeRequirementRepositoryInterface $attrRequiRepo
    ) {
        $this->beConstructedWith(
            $familyRepository,
            $familyFactory,
            $attributeRepository,
            $channelRepository,
            $attrRequiFactory,
            $attrRequiRepo
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Updater\FamilyUpdater');
    }

    function it_is_a_updater()
    {
        $this->shouldImplement('Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface');
    }

    function it_throws_an_exception_when_trying_to_update_anything_else_than_a_family()
    {
        $this->shouldThrow(
            new \InvalidArgumentException(
                'Expects a "Pim\Component\Catalog\Model\FamilyInterface", "stdClass" provided.'
            )
        )->during(
            'update',
            [new \stdClass(), []]
        );
    }

    function it_updates_a_family(
        $attrRequiFactory,
        $channelRepository,
        FamilyTranslation $translation,
        FamilyInterface $family,
        AttributeRepositoryInterface $attributeRepository,
        AttributeInterface $skuAttribute,
        AttributeInterface $nameAttribute,
        AttributeInterface $descAttribute,
        AttributeInterface $priceAttribute,
        AttributeRequirementInterface $skuMobileRqrmt,
        AttributeRequirementInterface $nameMobileRqrmt,
        AttributeRequirementInterface $skuPrintRqrmt,
        AttributeRequirementInterface $namePrintRqrmt,
        AttributeRequirementInterface $descPrintRqrmt,
        ChannelInterface $mobileChannel,
        ChannelInterface $printChannel
    ) {
        $values = [
            'code'                => 'mycode',
            'attributes'          => ['sku', 'name', 'description', 'price'],
            'attribute_as_label'  => 'name',
            'requirements'        => [
                'mobile' => ['sku', 'name'],
                'print'  => ['name', 'description'],
            ],
            'labels'              => [
                'fr_FR' => 'Moniteurs',
                'en_US' => 'PC Monitors',
            ],
        ];

        $family->getAttributeRequirements()->willReturn([$skuMobileRqrmt, $skuPrintRqrmt]);
        $family->getAttributes()->willReturn([$skuAttribute, $nameAttribute, $descAttribute, $priceAttribute]);
        $family->removeAttribute($nameAttribute)->shouldBeCalled();
        $family->removeAttribute($priceAttribute)->shouldBeCalled();
        $family->removeAttribute($descAttribute)->shouldBeCalled();
        $family->getId()->willReturn(42);

        $skuAttribute->getId()->willReturn(1);
        $nameAttribute->getId()->willReturn(2);
        $descAttribute->getId()->willReturn(3);
        $priceAttribute->getId()->willReturn(4);

        $skuMobileRqrmt->getAttribute()->willReturn($skuAttribute);
        $skuMobileRqrmt->getChannelCode()->willReturn('mobile');
        $skuPrintRqrmt->getAttribute()->willReturn($skuAttribute);
        $skuPrintRqrmt->getChannelCode()->willReturn('print');

        $attributeRepository->findOneByIdentifier('sku')->willReturn($skuAttribute);
        $attributeRepository->findOneByIdentifier('name')->willReturn($nameAttribute);
        $attributeRepository->findOneByIdentifier('description')->willReturn($descAttribute);
        $attributeRepository->findOneByIdentifier('price')->willReturn($priceAttribute);
        $attributeRepository->getIdentifier()->willReturn($skuAttribute);

        $skuAttribute->getAttributeType()->willReturn('pim_catalog_identifier');
        $nameAttribute->getAttributeType()->willReturn('pim_catalog_text');
        $descAttribute->getAttributeType()->willReturn('pim_catalog_textarea');
        $priceAttribute->getAttributeType()->willReturn('pim_catalog_price_collection');

        $channelRepository->getChannelCodes()->willReturn(['mobile', 'print']);
        $channelRepository->findOneByIdentifier('mobile')->willReturn($mobileChannel);
        $channelRepository->findOneByIdentifier('print')->willReturn($printChannel);

        $attrRequiFactory->createAttributeRequirement($nameAttribute, $mobileChannel, true)
            ->willReturn($nameMobileRqrmt);
        $attrRequiFactory->createAttributeRequirement($nameAttribute, $printChannel, true)->willReturn($namePrintRqrmt);
        $attrRequiFactory->createAttributeRequirement($descAttribute, $printChannel, true)->willReturn($descPrintRqrmt);

        $nameMobileRqrmt->getAttribute()->willReturn($nameAttribute);
        $namePrintRqrmt->getAttribute()->willReturn($nameAttribute);
        $descPrintRqrmt->getAttribute()->willReturn($descAttribute);

        $family
            ->setAttributeRequirements(
                [
                    $skuMobileRqrmt,
                    $skuPrintRqrmt,
                    $nameMobileRqrmt,
                    $namePrintRqrmt,
                    $descPrintRqrmt,
                ]
            )
            ->shouldBeCalled();

        $family->setCode('mycode')->shouldBeCalled();

        $family->addAttribute($skuAttribute)->shouldBeCalled();
        $family->addAttribute($nameAttribute)->shouldBeCalled();
        $family->addAttribute($skuAttribute)->shouldBeCalled();
        $family->addAttribute($skuAttribute)->shouldBeCalled();

        $family->setLocale('en_US')->shouldBeCalled();
        $family->setLocale('fr_FR')->shouldBeCalled();
        $family->getTranslation()->willReturn($translation);

        $translation->setLabel('label en us');
        $translation->setLabel('label fr fr');

        $family->addAttribute($skuAttribute)->shouldBeCalled();
        $family->addAttribute($nameAttribute)->shouldBeCalled();
        $family->addAttribute($descAttribute)->shouldBeCalled();
        $family->addAttribute($priceAttribute)->shouldBeCalled();

        $family->setAttributeAsLabel($nameAttribute)->shouldBeCalled();

        $this->update($family, $values, []);
    }

    public function it_should_not_remove_identifier_requirements_when_no_requirements_are_provided(
        FamilyInterface $family
    ) {
        $values = [
            'code' => 'mycode',
        ];

        $family->setCode('mycode')->shouldBeCalled();
        $family->setAttributeRequirements(Argument::any())->shouldNotBeCalled();

        $this->update($family, $values, []);
    }

    public function it_should_not_remove_identifier_requirements_when_empty_requirements_are_provided(
        $channelRepository,
        FamilyInterface $family,
        AttributeRepositoryInterface $attributeRepository,
        AttributeInterface $skuAttribute,
        AttributeRequirementInterface $skuMobileRqrmt,
        AttributeRequirementInterface $skuPrintRqrmt,
        ChannelInterface $mobileChannel,
        ChannelInterface $printChannel
    ) {
        $values = [
            'code' => 'mycode',
            'requirements' => []
        ];
        $family->getAttributeRequirements()->willReturn([$skuMobileRqrmt, $skuPrintRqrmt]);
        $skuMobileRqrmt->getAttribute()->willReturn($skuAttribute);
        $skuPrintRqrmt->getAttribute()->willReturn($skuAttribute);
        $skuMobileRqrmt->getChannelCode()->willReturn('mobile');
        $skuPrintRqrmt->getChannelCode()->willReturn('print');
        $skuAttribute->getAttributeType()->willReturn(AttributeTypes::IDENTIFIER);

        $channelRepository->getChannelCodes()->willReturn(['mobile', 'print']);
        $channelRepository->findOneByIdentifier('mobile')->willReturn($mobileChannel);
        $channelRepository->findOneByIdentifier('print')->willReturn($printChannel);
        $attributeRepository->getIdentifier()->willReturn($skuAttribute);

        $family->setCode('mycode')->shouldBeCalled();
        $family->setAttributeRequirements([$skuMobileRqrmt, $skuPrintRqrmt])->shouldBeCalled();

        $this->update($family, $values, []);
    }

    public function it_should_not_remove_identifier_requirements_when_other_requirements_are_provided(
        $attrRequiFactory,
        $channelRepository,
        FamilyInterface $family,
        AttributeRepositoryInterface $attributeRepository,
        AttributeInterface $skuAttribute,
        AttributeInterface $nameAttribute,
        AttributeInterface $descAttribute,
        AttributeRequirementInterface $skuMobileRqrmt,
        AttributeRequirementInterface $skuPrintRqrmt,
        AttributeRequirementInterface $namePrintRqrmt,
        AttributeRequirementInterface $descPrintRqrmt,
        ChannelInterface $mobileChannel,
        ChannelInterface $printChannel
    ) {
        $values = [
            'code' => 'mycode',
            'requirements' => [
                'print' => ['name', 'description']
            ]
        ];
        $family->getAttributeRequirements()->willReturn([$skuMobileRqrmt, $skuPrintRqrmt]);
        $family->getId()->willReturn(42);
        $skuMobileRqrmt->getAttribute()->willReturn($skuAttribute);
        $skuPrintRqrmt->getAttribute()->willReturn($skuAttribute);
        $skuMobileRqrmt->getChannelCode()->willReturn('mobile');
        $skuPrintRqrmt->getChannelCode()->willReturn('print');
        $skuAttribute->getAttributeType()->willReturn(AttributeTypes::IDENTIFIER);

        $channelRepository->findOneByIdentifier('print')->willReturn($printChannel);
        $attributeRepository->findOneByIdentifier('name')->willReturn($nameAttribute);
        $attributeRepository->findOneByIdentifier('description')->willReturn($descAttribute);
        $attrRequiFactory->createAttributeRequirement($nameAttribute, $printChannel, true)
            ->willReturn($namePrintRqrmt);
        $attrRequiFactory->createAttributeRequirement($descAttribute, $printChannel, true)
            ->willReturn($descPrintRqrmt);
        $namePrintRqrmt->getAttribute()->willReturn($nameAttribute);
        $descPrintRqrmt->getAttribute()->willReturn($descAttribute);

        $channelRepository->getChannelCodes()->willReturn(['mobile', 'print']);
        $channelRepository->findOneByIdentifier('mobile')->willReturn($mobileChannel);
        $channelRepository->findOneByIdentifier('print')->willReturn($printChannel);
        $attributeRepository->getIdentifier()->willReturn($skuAttribute);

        $family->setCode('mycode')->shouldBeCalled();
        $family->setAttributeRequirements(
            [$skuMobileRqrmt, $skuPrintRqrmt, $namePrintRqrmt, $descPrintRqrmt]
        )
        ->shouldBeCalled();

        $this->update($family, $values, []);
    }

    public function it_throws_an_exception_if_attribute_does_not_exist(
        $attributeRepository,
        FamilyInterface $family,
        AttributeInterface $priceAttribute
    ) {
        $data = [
            'code'                => 'mycode',
            'attributes'          => ['sku', 'name', 'description', 'price'],
            'attribute_as_label'  => 'name',
            'requirements'        => [
                'mobile' => ['sku', 'name'],
                'print'  => ['sku', 'name', 'description'],
            ],
            'labels'              => [
                'fr_FR' => 'Moniteurs',
                'en_US' => 'PC Monitors',
            ],
        ];

        $family->setCode('mycode')->shouldBeCalled();
        $family->getAttributes()->willReturn([$priceAttribute]);
        $family->removeAttribute($priceAttribute)->shouldBeCalled();

        $attributeRepository->findOneByIdentifier('sku')->willReturn(null);

        $this->shouldThrow(new \InvalidArgumentException(sprintf('Attribute with "%s" code does not exist', 'sku')))
            ->during('update', [$family, $data]);
    }

    function it_throws_an_exception_if_channel_not_found(
        $channelRepository,
        $attributeRepository,
        AttributeInterface $attribute,
        FamilyInterface $family
    ) {
        $data = [
            'code'                => 'mycode',
            'requirements'        => [
                'mobile' => ['sku', 'name'],
                'print'  => ['sku', 'name', 'description'],
            ]
        ];
        $family->getAttributeRequirements()->willReturn([]);
        $family->setCode('mycode')->shouldBeCalled();

        $attributeRepository->findOneByIdentifier('sku')->willReturn($attribute);
        $attributeRepository->findOneByIdentifier('name')->willReturn($attribute);
        $attributeRepository->findOneByIdentifier('description')->willReturn($attribute);
        $attributeRepository->findOneByIdentifier('price')->willReturn($attribute);
        $channelRepository->findOneByIdentifier('print')->willReturn(null);
        $channelRepository->findOneByIdentifier('mobile')->willReturn(null);

        $this->shouldThrow(new \InvalidArgumentException(sprintf('Channel with "%s" code does not exist', 'mobile')))
            ->during('update', [$family, $data]);
    }
}
