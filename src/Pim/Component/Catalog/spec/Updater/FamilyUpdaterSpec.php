<?php

namespace spec\Pim\Component\Catalog\Updater;

use Akeneo\Component\Localization\TranslatableUpdater;
use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Component\StorageUtils\Exception\UnknownPropertyException;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\FamilyTranslation;
use Pim\Component\Catalog\Factory\AttributeRequirementFactory;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeRequirementInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\AttributeRequirementRepositoryInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\FamilyRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;

class FamilyUpdaterSpec extends ObjectBehavior
{
    function let(
        FamilyRepositoryInterface $familyRepository,
        AttributeRepositoryInterface $attributeRepository,
        ChannelRepositoryInterface $channelRepository,
        AttributeRequirementFactory $attrRequiFactory,
        AttributeRequirementRepositoryInterface $attributeRequirementRepo,
        TranslatableUpdater $translatableUpdater
    ) {
        $this->beConstructedWith(
            $familyRepository,
            $attributeRepository,
            $channelRepository,
            $attrRequiFactory,
            $attributeRequirementRepo,
            $translatableUpdater
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
            InvalidObjectException::objectExpected(
                'stdClass',
                'Pim\Component\Catalog\Model\FamilyInterface'
            )
        )->during(
            'update',
            [new \stdClass(), []]
        );
    }

    function it_updates_a_family(
        $attrRequiFactory,
        $channelRepository,
        $attributeRequirementRepo,
        $translatableUpdater,
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
            'code'                   => 'mycode',
            'attributes'             => ['sku', 'name', 'description', 'price'],
            'attribute_as_label'     => 'name',
            'attribute_requirements' => [
                'mobile' => ['sku', 'name'],
                'print'  => ['name', 'description'],
            ],
            'labels'                 => [
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

        $skuAttribute->getCode()->willReturn('sku');
        $skuAttribute->getType()->willReturn(AttributeTypes::IDENTIFIER);

        $skuPrintRqrmt->getAttribute()->willReturn($skuAttribute);
        $skuPrintRqrmt->getChannelCode()->willReturn('print');

        $family->removeAttributeRequirement($skuMobileRqrmt)->shouldNotBeCalled();
        $family->removeAttributeRequirement($skuPrintRqrmt)->shouldNotBeCalled();

        $attributeRepository->findOneByIdentifier('name')->willReturn($nameAttribute);
        $attributeRepository->findOneByIdentifier('description')->willReturn($descAttribute);

        $channelRepository->findOneByIdentifier('mobile')->willReturn($mobileChannel);
        $channelRepository->findOneByIdentifier('print')->willReturn($printChannel);

        $mobileChannel->getId()->willReturn(1);
        $printChannel->getId()->willReturn(2);

        $attributeRequirementRepo->findOneBy([
            'attribute' => 2,
            'channel' => 1,
            'family' => 42
        ])->willReturn($nameMobileRqrmt);
        $attributeRequirementRepo->findOneBy([
            'attribute' => 2,
            'channel' => 2,
            'family' => 42
        ])->willReturn(null);
        $attributeRequirementRepo->findOneBy([
            'attribute' => 3,
            'channel' => 2,
            'family' => 42
        ])->willReturn($descPrintRqrmt);

        $attrRequiFactory->createAttributeRequirement($nameAttribute, $printChannel, true)->willReturn($namePrintRqrmt);

        $family->addAttributeRequirement($nameMobileRqrmt)->shouldBeCalled();
        $family->addAttributeRequirement($descPrintRqrmt)->shouldBeCalled();
        $family->addAttributeRequirement($namePrintRqrmt)->shouldBeCalled();

        $attributeRepository->findOneByIdentifier('sku')->willReturn($skuAttribute);
        $attributeRepository->findOneByIdentifier('price')->willReturn($priceAttribute);

        $nameAttribute->getType()->willReturn(AttributeTypes::TEXT);
        $descAttribute->getType()->willReturn(AttributeTypes::TEXTAREA);
        $priceAttribute->getType()->willReturn(AttributeTypes::PRICE_COLLECTION);

        $family->setCode('mycode')->shouldBeCalled();

        $family->addAttribute($skuAttribute)->shouldBeCalled();
        $family->addAttribute($nameAttribute)->shouldBeCalled();
        $family->addAttribute($skuAttribute)->shouldBeCalled();
        $family->addAttribute($skuAttribute)->shouldBeCalled();


        $translatableUpdater->update($family, ['fr_FR' => 'Moniteurs', 'en_US' => 'PC Monitors'])->shouldBeCalled();

        $family->addAttribute($skuAttribute)->shouldBeCalled();
        $family->addAttribute($nameAttribute)->shouldBeCalled();
        $family->addAttribute($descAttribute)->shouldBeCalled();
        $family->addAttribute($priceAttribute)->shouldBeCalled();

        $family->setAttributeAsLabel($nameAttribute)->shouldBeCalled();

        $this->update($family, $values, []);
    }

    function it_does_not_remove_identifier_requirements_when_no_requirements_are_provided(
        FamilyInterface $family
    ) {
        $values = [
            'code' => 'mycode',
        ];

        $family->setCode('mycode')->shouldBeCalled();
        $family->setAttributeRequirements(Argument::any())->shouldNotBeCalled();

        $this->update($family, $values, []);
    }

    function it_does_not_remove_requirements_when_channel_column_is_missing(
        $channelRepository,
        ChannelInterface $mobileChannel,
        FamilyInterface $family,
        AttributeInterface $skuAttribute,
        AttributeRequirementInterface $skuMobileRqrmt,
        AttributeRequirementInterface $skuEcommerceRqrmt,
        AttributeRequirementInterface $nameEcommerceRqrmt
    ) {
        $channelRepository->findOneByIdentifier('mobile')->willReturn($mobileChannel);

        $values = [
            'attribute_requirements' => [
                'mobile' => ['sku']
            ],
            'code'                   => 'mycode'
        ];
        $family->getAttributeRequirements()->willReturn([
            'sku_ecommerce'  => $skuEcommerceRqrmt,
            'name_ecommerce' => $nameEcommerceRqrmt,
            'sku_mobile'     => $skuMobileRqrmt
        ]);

        $family->setCode('mycode')->shouldBeCalled();
        $skuEcommerceRqrmt->getChannelCode()->willReturn('ecommerce');
        $skuMobileRqrmt->getChannelCode()->willReturn('mobile');
        $nameEcommerceRqrmt->getChannelCode()->willReturn('ecommerce');

        $skuMobileRqrmt->getAttribute()->willReturn($skuAttribute);

        $skuAttribute->getCode()->willReturn('sku');

        $family->removeAttributeRequirement($nameEcommerceRqrmt)->shouldNotBeCalled();
        $family->removeAttributeRequirement($skuEcommerceRqrmt)->shouldNotBeCalled();
        $family->removeAttributeRequirement($skuMobileRqrmt)->shouldNotBeCalled();

        $family->addAttributeRequirement($nameEcommerceRqrmt)->shouldNotBeCalled();
        $family->addAttributeRequirement($skuEcommerceRqrmt)->shouldNotBeCalled();
        $family->addAttributeRequirement($skuMobileRqrmt)->shouldNotBeCalled();

        $this->update($family, $values, []);
    }

    function it_does_not_remove_identifier_requirements_when_empty_requirements_are_provided(
        FamilyInterface $family,
        AttributeRequirementInterface $skuMobileRqrmt,
        AttributeRequirementInterface $skuPrintRqrmt
    ) {
        $values = [
            'attribute_requirements' => []
        ];
        $family->getAttributeRequirements()->willReturn([$skuMobileRqrmt, $skuPrintRqrmt]);

        $skuMobileRqrmt->getChannelCode()->willReturn('mobile');
        $skuPrintRqrmt->getChannelCode()->willReturn('print');

        $family->removeAttributeRequirement($skuMobileRqrmt)->shouldNotBeCalled();
        $family->removeAttributeRequirement($skuPrintRqrmt)->shouldNotBeCalled();
        $family->addAttributeRequirement($skuMobileRqrmt)->shouldNotBeCalled();
        $family->addAttributeRequirement($skuPrintRqrmt)->shouldNotBeCalled();

        $this->update($family, $values, []);
    }

    function it_does_not_remove_identifier_requirements_when_other_requirements_are_provided(
        $attrRequiFactory,
        $channelRepository,
        $attributeRepository,
        $attributeRequirementRepo,
        FamilyInterface $family,
        AttributeInterface $skuAttribute,
        AttributeInterface $nameAttribute,
        AttributeInterface $descriptionAttribute,
        AttributeRequirementInterface $skuMobileRqrmt,
        AttributeRequirementInterface $skuPrintRqrmt,
        AttributeRequirementInterface $namePrintRqrmt,
        AttributeRequirementInterface $descPrintRqrmt,
        ChannelInterface $printChannel
    ) {
        $values = [
            'code'                   => 'mycode',
            'attribute_requirements' => [
                'print' => ['name', 'description']
            ]
        ];

        $family->setCode('mycode')->shouldBeCalled();
        $family->getAttributeRequirements()->willReturn([$skuMobileRqrmt, $skuPrintRqrmt]);

        $skuMobileRqrmt->getChannelCode()->willReturn('mobile');

        $skuPrintRqrmt->getChannelCode()->willReturn('print');
        $skuPrintRqrmt->getAttribute()->willReturn($skuAttribute);

        $skuAttribute->getCode()->willReturn('sku');
        $skuAttribute->getType()->willReturn(AttributeTypes::IDENTIFIER);

        $family->removeAttributeRequirement($skuMobileRqrmt)->shouldNotBeCalled();
        $family->removeAttributeRequirement($skuPrintRqrmt)->shouldNotBeCalled();

        $attributeRepository->findOneByIdentifier('name')->willReturn($nameAttribute);
        $attributeRepository->findOneByIdentifier('description')->willReturn($descriptionAttribute);

        $channelRepository->findOneByIdentifier('print')->willReturn($printChannel);

        $printChannel->getId()->willReturn('1');
        $nameAttribute->getId()->willReturn('1');
        $descriptionAttribute->getId()->willReturn('2');
        $nameAttribute->getType()->willReturn('text');
        $descriptionAttribute->getType()->willReturn('text');
        $family->getId()->willReturn('1');

        $attributeRequirementRepo->findOneBy([
            'attribute' => '1',
            'channel' => '1',
            'family' => '1'
        ])->willReturn(null);
        $attributeRequirementRepo->findOneBy([
            'attribute' => '2',
            'channel' => '1',
            'family' => '1'
        ])->willReturn(null);

        $attrRequiFactory->createAttributeRequirement($nameAttribute, $printChannel, true)->willReturn($namePrintRqrmt);
        $attrRequiFactory->createAttributeRequirement(
            $descriptionAttribute,
            $printChannel,
            true
        )->willReturn($descPrintRqrmt);

        $family->addAttributeRequirement($namePrintRqrmt)->shouldBeCalled();
        $family->addAttributeRequirement($descPrintRqrmt)->shouldBeCalled();

        $this->update($family, $values, []);
    }

    function it_throws_an_exception_if_attribute_does_not_exist(
        $attributeRepository,
        FamilyInterface $family,
        AttributeInterface $priceAttribute
    ) {
        $data = [
            'code'                   => 'mycode',
            'attributes'             => ['sku', 'name', 'description', 'price'],
            'attribute_as_label'     => 'name',
            'attribute_requirements' => [
                'mobile' => ['sku', 'name'],
                'print'  => ['sku', 'name', 'description'],
            ],
            'labels'                 => [
                'fr_FR' => 'Moniteurs',
                'en_US' => 'PC Monitors',
            ],
        ];

        $family->setCode('mycode')->shouldBeCalled();
        $family->getAttributes()->willReturn([$priceAttribute]);
        $family->removeAttribute($priceAttribute)->shouldBeCalled();

        $attributeRepository->findOneByIdentifier('sku')->willReturn(null);

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'attributes',
                'code',
                'The attribute does not exist',
                'Pim\Component\Catalog\Updater\FamilyUpdater',
                'sku'
            )
        )->during('update', [$family, $data]);
    }

    public function it_throws_an_exception_if_required_attribute_does_not_exist(
        $attributeRepository,
        $channelRepository,
        FamilyInterface $family,
        AttributeInterface $attribute,
        ChannelInterface $channel
    ) {
        $data = [
            'code'                   => 'mycode',
            'attribute_requirements' => [
                'mobile' => ['sku', 'name'],
                'print'  => ['sku', 'name', 'description'],
            ]
        ];
        $family->getAttributeRequirements()->willReturn([]);
        $family->setCode('mycode')->shouldBeCalled();

        $attributeRepository->findOneByIdentifier('sku')->willReturn(null);
        $attributeRepository->findOneByIdentifier('name')->willReturn($attribute);
        $attributeRepository->findOneByIdentifier('description')->willReturn($attribute);
        $channelRepository->findOneByIdentifier('print')->willReturn($channel);
        $channelRepository->findOneByIdentifier('mobile')->willReturn($channel);

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'attribute_requirements',
                'code',
                'The attribute does not exist',
                'Pim\Component\Catalog\Updater\FamilyUpdater',
                'sku'
            )
        )->during('update', [$family, $data]);
    }

    public function it_throws_an_exception_if_attribute_as_label_does_not_exist(
        $attributeRepository,
        FamilyInterface $family
    ) {
        $data = [
            'attribute_as_label'     => 'unknown',
            'code'                   => 'mycode',
            'attributes'             => ['sku', 'name', 'description', 'price'],
            'attribute_requirements' => [
                'mobile' => ['sku', 'name'],
                'print'  => ['sku', 'name', 'description'],
            ],
            'labels'                 => [
                'fr_FR' => 'Moniteurs',
                'en_US' => 'PC Monitors',
            ],
        ];

        $attributeRepository->findOneByIdentifier('unknown')->willReturn(null);

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'attribute_as_label',
                'code',
                'The attribute does not exist',
                'Pim\Component\Catalog\Updater\FamilyUpdater',
                'unknown'
            )
        )->during('update', [$family, $data]);
    }

    function it_throws_an_exception_if_channel_not_found(
        $channelRepository,
        $attributeRepository,
        AttributeInterface $attribute,
        FamilyInterface $family
    ) {
        $data = [
            'code'                   => 'mycode',
            'attribute_requirements' => [
                'ecommerce' => ['sku'],
                'mobile'    => ['sku', 'name'],
                'print'     => ['sku', 'name', 'description'],
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
        $channelRepository->findOneByIdentifier('ecommerce')->willReturn(null);

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'attribute_requirements',
                'code',
                'The channel does not exist',
                'Pim\Component\Catalog\Updater\FamilyUpdater',
                'ecommerce'
            )
        )->during('update', [$family, $data]);
    }

    function it_throws_an_exception_when_trying_to_update_a_non_existent_field(FamilyInterface $family)
    {
        $data = [
            'unknown_field' => 'field',
        ];

        $this->shouldThrow(
                UnknownPropertyException::unknownProperty(
                    'unknown_field',
                    new NoSuchPropertyException()
                )
        )->during('update', [$family, $data, []]);
    }

    function it_throws_an_exception_when_code_is_not_a_scalar(FamilyInterface $family)
    {
        $data = [
            'code' => [],
        ];

        $this
            ->shouldThrow(
                InvalidPropertyTypeException::scalarExpected(
                    'code',
                    'Pim\Component\Catalog\Updater\FamilyUpdater',
                    []
                )
            )
            ->during('update', [$family, $data, []]);
    }

    function it_throws_an_exception_when_attribute_as_label_is_not_a_scalar(FamilyInterface $family)
    {
        $data = [
            'attribute_as_label' => [],
        ];

        $this
            ->shouldThrow(
                InvalidPropertyTypeException::scalarExpected(
                    'attribute_as_label',
                    'Pim\Component\Catalog\Updater\FamilyUpdater',
                    []
                )
            )
            ->during('update', [$family, $data, []]);
    }

    function it_throws_an_exception_when_attributes_is_not_an_array(FamilyInterface $family)
    {
        $data = [
            'attributes' => 'foo',
        ];

        $this
            ->shouldThrow(
                InvalidPropertyTypeException::arrayExpected(
                    'attributes',
                    'Pim\Component\Catalog\Updater\FamilyUpdater',
                    'foo'
                )
            )
            ->during('update', [$family, $data, []]);
    }

    function it_throws_an_exception_when_a_value_in_attributes_array_is_not_a_scalar(FamilyInterface $family)
    {
        $data = [
            'attributes' => ['foo', []],
        ];

        $this
            ->shouldThrow(
                InvalidPropertyTypeException::validArrayStructureExpected(
                    'attributes',
                    'one of the attributes is not a scalar',
                    'Pim\Component\Catalog\Updater\FamilyUpdater',
                    ['foo', []]
                )
            )
            ->during('update', [$family, $data, []]);
    }

    function it_throws_an_exception_when_labels_is_not_an_array(FamilyInterface $family)
    {
        $data = [
            'labels' => 'foo',
        ];

        $this
            ->shouldThrow(
                InvalidPropertyTypeException::arrayExpected(
                    'labels',
                    'Pim\Component\Catalog\Updater\FamilyUpdater',
                    'foo'
                )
            )
            ->during('update', [$family, $data, []]);
    }

    function it_throws_an_exception_when_a_value_in_labels_array_is_not_a_scalar(FamilyInterface $family)
    {
        $data = [
            'labels' => [
                'en_US' => 'us_Label',
                'fr_FR' => [],
            ],
        ];

        $this
            ->shouldThrow(
                InvalidPropertyTypeException::validArrayStructureExpected(
                    'labels',
                    'one of the labels is not a scalar',
                    'Pim\Component\Catalog\Updater\FamilyUpdater',
                    ['en_US' => 'us_Label', 'fr_FR' => []]
                )
            )
            ->during('update', [$family, $data, []]);
    }

    function it_throws_an_exception_when_attribute_requirements_is_not_an_array(FamilyInterface $family)
    {
        $data = [
            'attribute_requirements' => 'foo',
        ];

        $this
            ->shouldThrow(
                InvalidPropertyTypeException::arrayExpected('attribute_requirements', 'update', 'family', 'foo')
            )
            ->during('update', [$family, $data, []]);
    }

    function it_throws_an_exception_when_a_value_in_attribute_requirements_is_not_an_array(FamilyInterface $family)
    {
        $data = [
            'attribute_requirements' => [
                'ecommerce' => ['sku'],
                'tablet'    => 'foo',
            ],
        ];

        $this
            ->shouldThrow(
                InvalidPropertyTypeException::validArrayStructureExpected(
                    'attribute_requirements',
                    'the channel "tablet" is not an array',
                    'Pim\Component\Catalog\Updater\FamilyUpdater',
                    ['ecommerce' => ['sku'], 'tablet' => 'foo']
                )
            )
            ->during('update', [$family, $data, []]);
    }

    function it_throws_an_exception_when_an_attribute_in_attribute_requirements_is_not_a_scalar(FamilyInterface $family)
    {
        $data = [
            'attribute_requirements' => [
                'ecommerce' => ['sku'],
                'tablet'    => ['foo', []],
            ],
        ];

        $this
            ->shouldThrow(
                InvalidPropertyTypeException::validArrayStructureExpected(
                    'attribute_requirements',
                    'one of the attributes in the channel "tablet" is not a scalar',
                    'Pim\Component\Catalog\Updater\FamilyUpdater',
                    ['ecommerce' => ['sku'], 'tablet' => ['foo', []]]
                )
            )
            ->during('update', [$family, $data, []]);
    }
}
