<?php

namespace Specification\Akeneo\Pim\Structure\Component\Updater;

use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Channel\Infrastructure\Component\Model\Locale;
use Akeneo\Channel\Infrastructure\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Factory\AttributeRequirementFactory;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeRequirement;
use Akeneo\Pim\Structure\Component\Model\AttributeRequirementInterface;
use Akeneo\Pim\Structure\Component\Model\Family;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRequirementRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;
use Akeneo\Pim\Structure\Component\Updater\FamilyUpdater;
use Akeneo\Tool\Component\Localization\TranslatableUpdater;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
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
        TranslatableUpdater $translatableUpdater,
        IdentifiableObjectRepositoryInterface $localeRepository,

    ) {
        $this->beConstructedWith(
            $familyRepository,
            $attributeRepository,
            $channelRepository,
            $attrRequiFactory,
            $attributeRequirementRepo,
            $translatableUpdater,
            $localeRepository
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(FamilyUpdater::class);
    }

    function it_is_a_updater()
    {
        $this->shouldImplement(ObjectUpdaterInterface::class);
    }

    function it_throws_an_exception_when_trying_to_update_anything_else_than_a_family()
    {
        $this->shouldThrow(
            InvalidObjectException::objectExpected(
                'stdClass',
                FamilyInterface::class
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
        FamilyInterface $family,
        AttributeRepositoryInterface $attributeRepository,
        AttributeInterface $skuAttribute,
        AttributeInterface $nameAttribute,
        AttributeInterface $descAttribute,
        AttributeInterface $pictureAttribute,
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
            'attributes'             => ['sku', 'name', 'description', 'picture'],
            'attribute_as_label'     => 'name',
            'attribute_as_image'     => 'picture',
            'attribute_requirements' => [
                'mobile' => ['sku', 'name'],
                'print'  => ['name', 'description'],
            ],
            'labels'                 => [
                'fr_FR' => 'Moniteurs',
                'en_US' => 'PC Monitors',
            ],
        ];

        $family->getCreated()->willReturn(new \DateTime());
        $family->getAttributeRequirements()->willReturn([$skuMobileRqrmt, $skuPrintRqrmt]);
        $family->getAttributes()->willReturn([$skuAttribute, $nameAttribute, $descAttribute, $pictureAttribute]);
        $family->getId()->willReturn(42);

        $skuAttribute->getCode()->willReturn('sku');
        $skuAttribute->getType()->willReturn(AttributeTypes::IDENTIFIER);
        $skuAttribute->getId()->willReturn(1);

        $nameAttribute->getCode()->willReturn('name');
        $nameAttribute->getType()->willReturn(AttributeTypes::TEXT);
        $nameAttribute->getId()->willReturn(2);

        $descAttribute->getCode()->willReturn('description');
        $descAttribute->getType()->willReturn(AttributeTypes::TEXTAREA);
        $descAttribute->getId()->willReturn(3);

        $pictureAttribute->getCode()->willReturn('picture');
        $pictureAttribute->getType()->willReturn(AttributeTypes::IMAGE);
        $pictureAttribute->getId()->willReturn(4);

        $skuMobileRqrmt->getAttribute()->willReturn($skuAttribute);
        $skuMobileRqrmt->getChannelCode()->willReturn('mobile');
        $skuMobileRqrmt->isRequired()->willReturn(true);

        $skuPrintRqrmt->getAttribute()->willReturn($skuAttribute);
        $skuPrintRqrmt->getChannelCode()->willReturn('print');

        $attributeRepository->findOneByIdentifier('name')->willReturn($nameAttribute);
        $attributeRepository->findOneByIdentifier('description')->willReturn($descAttribute);

        $family->updateAttributes([$skuAttribute, $nameAttribute, $descAttribute, $pictureAttribute]);

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
        $family->removeAttributeRequirement($skuPrintRqrmt)->shouldBeCalled();

        $attributeRepository->findOneByIdentifier('sku')->willReturn($skuAttribute);
        $attributeRepository->findOneByIdentifier('picture')->willReturn($pictureAttribute);

        $family->setCode('mycode')->shouldBeCalled();

        $translatableUpdater->update($family, ['fr_FR' => 'Moniteurs', 'en_US' => 'PC Monitors'])->shouldBeCalled();

        $family->setAttributeAsLabel($nameAttribute)->shouldBeCalled();
        $family->setAttributeAsImage($pictureAttribute)->shouldBeCalled();

        $this->update($family, $values, []);
    }

    function it_updates_a_family_without_changing_attributes_when_they_are_the_same(
        AttributeRepositoryInterface $attributeRepository,
        FamilyInterface $family,
        AttributeInterface $skuAttribute,
        AttributeInterface $nameAttribute,
        AttributeInterface $descAttribute
    ) {
        $values = [
            'attributes' => ['sku', 'name', 'description']
        ];

        $family->getCreated()->willReturn(new \DateTime());
        $family->getAttributes()->willReturn([$skuAttribute, $nameAttribute, $descAttribute]);

        $skuAttribute->getCode()->willReturn('sku');
        $nameAttribute->getCode()->willReturn('name');
        $descAttribute->getCode()->willReturn('description');

        $attributeRepository->findOneByIdentifier('sku')->willReturn($skuAttribute);
        $attributeRepository->findOneByIdentifier('name')->willReturn($nameAttribute);
        $attributeRepository->findOneByIdentifier('description')->willReturn($descAttribute);

        $family->updateAttributes([$skuAttribute, $nameAttribute, $descAttribute])->shouldBeCalled();

        $this->update($family, $values, []);
    }

    function it_updates_a_family_by_removing_an_attribute(
        AttributeRepositoryInterface $attributeRepository,
        FamilyInterface $family,
        AttributeInterface $skuAttribute,
        AttributeInterface $nameAttribute,
        AttributeInterface $descAttribute
    ) {
        $values = [
            'attributes' => ['sku', 'name']
        ];

        $family->getCreated()->willReturn(new \DateTime());
        $family->getAttributes()->willReturn([$skuAttribute, $nameAttribute, $descAttribute]);

        $skuAttribute->getCode()->willReturn('sku');
        $nameAttribute->getCode()->willReturn('name');
        $descAttribute->getCode()->willReturn('description');
        $descAttribute->getType()->willReturn(AttributeTypes::TEXTAREA);

        $attributeRepository->findOneByIdentifier('sku')->willReturn($skuAttribute);
        $attributeRepository->findOneByIdentifier('name')->willReturn($nameAttribute);
        $attributeRepository->findOneByIdentifier('description')->willReturn($descAttribute);

        $family->updateAttributes([$skuAttribute, $nameAttribute])->shouldBeCalled();

        $this->update($family, $values, []);
    }

    function it_does_not_remove_identifier_requirements_when_no_requirements_are_provided(
        FamilyInterface $family
    ) {
        $values = [
            'code' => 'mycode',
        ];

        $family->getCreated()->willReturn(new \DateTime());
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
        $family->getCreated()->willReturn(new \DateTime());
        $family->getAttributeRequirements()->willReturn([
            'sku_ecommerce'  => $skuEcommerceRqrmt,
            'name_ecommerce' => $nameEcommerceRqrmt,
            'sku_mobile'     => $skuMobileRqrmt
        ]);

        $family->setCode('mycode')->shouldBeCalled();
        $skuEcommerceRqrmt->getChannelCode()->willReturn('ecommerce');
        $skuEcommerceRqrmt->isRequired()->willReturn(true);
        $skuMobileRqrmt->getChannelCode()->willReturn('mobile');
        $skuMobileRqrmt->isRequired()->willReturn(true);
        $nameEcommerceRqrmt->getChannelCode()->willReturn('ecommerce');
        $nameEcommerceRqrmt->isRequired()->willReturn();

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

        $family->getCreated()->willReturn(new \DateTime());
        $family->setCode('mycode')->shouldBeCalled();
        $family->getAttributes()->willReturn([$priceAttribute]);

        $attributeRepository->findOneByIdentifier('sku')->willReturn(null);

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'attributes',
                'code',
                'The attribute does not exist',
                FamilyUpdater::class,
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
        $family->getCreated()->willReturn(new \DateTime());
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
                FamilyUpdater::class,
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
        $family->getCreated()->willReturn(new \DateTime());
        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'attribute_as_label',
                'code',
                'The attribute does not exist',
                FamilyUpdater::class,
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
        $family->getCreated()->willReturn(new \DateTime());
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
                FamilyUpdater::class,
                'ecommerce'
            )
        )->during('update', [$family, $data]);
    }

    function it_throws_an_exception_when_trying_to_update_a_non_existent_field(FamilyInterface $family)
    {
        $data = [
            'unknown_field' => 'field',
        ];
        $family->getCreated()->willReturn(new \DateTime());
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
        $family->getCreated()->willReturn(new \DateTime());
        $this
            ->shouldThrow(
                InvalidPropertyTypeException::scalarExpected(
                    'code',
                    FamilyUpdater::class,
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
        $family->getCreated()->willReturn(new \DateTime());
        $this
            ->shouldThrow(
                InvalidPropertyTypeException::scalarExpected(
                    'attribute_as_label',
                    FamilyUpdater::class,
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
        $family->getCreated()->willReturn(new \DateTime());
        $this
            ->shouldThrow(
                InvalidPropertyTypeException::arrayExpected(
                    'attributes',
                    FamilyUpdater::class,
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
        $family->getCreated()->willReturn(new \DateTime());
        $this
            ->shouldThrow(
                InvalidPropertyTypeException::validArrayStructureExpected(
                    'attributes',
                    'one of the attributes is not a scalar',
                    FamilyUpdater::class,
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
        $family->getCreated()->willReturn(new \DateTime());
        $this
            ->shouldThrow(
                InvalidPropertyTypeException::arrayExpected(
                    'labels',
                    FamilyUpdater::class,
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
        $family->getCreated()->willReturn(new \DateTime());
        $this
            ->shouldThrow(
                InvalidPropertyTypeException::validArrayStructureExpected(
                    'labels',
                    'one of the labels is not a scalar',
                    FamilyUpdater::class,
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
        $family->getCreated()->willReturn(new \DateTime());
        $this
            ->shouldThrow(
                InvalidPropertyTypeException::arrayExpected('attribute_requirements', FamilyUpdater::class, 'foo')
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
        $family->getCreated()->willReturn(new \DateTime());
        $this
            ->shouldThrow(
                InvalidPropertyTypeException::validArrayStructureExpected(
                    'attribute_requirements',
                    'the channel "tablet" is not an array',
                    FamilyUpdater::class,
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
        $family->getCreated()->willReturn(new \DateTime());
        $this
            ->shouldThrow(
                InvalidPropertyTypeException::validArrayStructureExpected(
                    'attribute_requirements',
                    'one of the attributes in the channel "tablet" is not a scalar',
                    FamilyUpdater::class,
                    ['ecommerce' => ['sku'], 'tablet' => ['foo', []]]
                )
            )
            ->during('update', [$family, $data, []]);
    }

    function it_sets_requirement_as_required_for_a_new_channel
    (
        $channelRepository,
        $attributeRequirementRepo,
        FamilyInterface $family,
        AttributeRepositoryInterface $attributeRepository,
        AttributeInterface $skuAttribute,
        AttributeInterface $nameAttribute,
        AttributeRequirementInterface $skuNewChannelRqrmt,
        AttributeRequirementInterface $nameNewChannelRqrmt,
        ChannelInterface $newChannel
    ) {
        $data = [
            'code'                   => 'mycode',
            'attribute_requirements' => [
                'new_channel' => ['name'],
            ]
        ];
        $family->getCreated()->willReturn(new \DateTime());
        $family->getAttributeRequirements()->willReturn([$skuNewChannelRqrmt, $nameNewChannelRqrmt]);
        $family->getId()->willReturn(1);

        $skuAttribute->getCode()->willReturn('sku');
        $skuAttribute->getType()->willReturn(AttributeTypes::IDENTIFIER);
        $skuAttribute->getId()->willReturn(1);

        $nameAttribute->getCode()->willReturn('name');
        $nameAttribute->getType()->willReturn(AttributeTypes::TEXT);
        $nameAttribute->getId()->willReturn(2);

        $skuNewChannelRqrmt->getChannelCode()->willReturn('new_channel');
        $skuNewChannelRqrmt->getAttribute()->willReturn($skuAttribute);
        $skuNewChannelRqrmt->isRequired()->willReturn(true);

        $nameNewChannelRqrmt->getChannelCode()->willReturn('new_channel');
        $nameNewChannelRqrmt->getAttribute()->willReturn($nameAttribute);
        $nameNewChannelRqrmt->isRequired()->willReturn(false);

        $newChannel->getId()->willReturn(44);

        $channelRepository->findOneByIdentifier('new_channel')->willReturn($newChannel);
        $attributeRepository->findOneByIdentifier('sku')->willReturn($skuAttribute);
        $attributeRepository->findOneByIdentifier('name')->willReturn($nameAttribute);

        $attributeRequirementRepo->findOneBy(
            [
                'attribute' => 1,
                'channel' => 44,
                'family' => 1,
            ]
        )->willReturn($skuNewChannelRqrmt);
        $attributeRequirementRepo->findOneBy(
            [
                'attribute' => 2,
                'channel'   => 44,
                'family'    => 1,
            ]
        )->willReturn($nameNewChannelRqrmt);

        $family->setCode('mycode')->shouldBeCalled();
        $nameNewChannelRqrmt->setRequired(true)->shouldBeCalled();
        $family->addAttributeRequirement($nameNewChannelRqrmt)->shouldBeCalled();
        $family->removeAttributeRequirement($skuNewChannelRqrmt)->shouldBeCalled();

        $this->update($family, $data);
    }

    function it_does_locale_code_normalization_when_updating_labels(
        $localeRepository,
        $translatableUpdater,
    ) {
        $inputLabels = [
            'fr_FR' => 'Tablette',
            'EN_us' => 'Tablet',
            'foo_bar' => 'Tablefoo'
        ];
        $normalizedLabels = [
            'fr_FR' => 'Tablette',
            'en_US' => 'Tablet',
            'foo_bar' => 'Tablefoo'
        ];
        $values = [
            'code' => 'ecommerce',
            'labels' => $inputLabels,
        ];
        $localeRepository->findOneByIdentifier('EN_us')->willReturn($this->makeLocale('en_US')); // by case-insentive matching BDD-side
        $localeRepository->findOneByIdentifier('fr_FR')->willReturn($this->makeLocale('fr_FR'));
        $localeRepository->findOneByIdentifier('foo_bar')->willReturn(null); // unknown to PIM

        $family = new Family();
        $family->setCreated(new \DateTime());
        $this->update($family, $values, []);

        $translatableUpdater->update(Argument::type(FamilyInterface::class), $normalizedLabels)->shouldBeCalled();
    }

    function it_updates_a_family_without_removing_sku_on_new_families(
        $attrRequiFactory,
        $channelRepository,
        $attributeRequirementRepo,
        FamilyInterface $family,
        AttributeRepositoryInterface $attributeRepository,
        AttributeInterface $skuAttribute,
        AttributeInterface $nameAttribute,
        AttributeInterface $descAttribute,
        AttributeRequirementInterface $skuMobileRqrmt,
        AttributeRequirementInterface $nameMobileRqrmt,
        AttributeRequirementInterface $skuPrintRqrmt,
        AttributeRequirementInterface $namePrintRqrmt,
        AttributeRequirementInterface $descPrintRqrmt,
        ChannelInterface $mobileChannel,
        ChannelInterface $printChannel,
    ) {
        $values = [
            'code'                   => 'mycode',
            'attributes'             => ['sku', 'name', 'description'],
            'attribute_requirements' => [
                'mobile' => ['sku', 'name'],
                'print'  => ['name', 'description'], // here sku is not declared but should be added (as its a new family)
            ],
        ];

        $channelRepository->findAll()->shouldBeCalled()->willReturn([$mobileChannel, $printChannel]);

        $family->getCreated()->willReturn(null);
        $family->getAttributeRequirements()->willReturn([$skuMobileRqrmt, $skuPrintRqrmt]);
        $family->getAttributes()->willReturn([$skuAttribute, $nameAttribute, $descAttribute]);
        $family->getId()->willReturn(42);

        $skuAttribute->getCode()->willReturn('sku');
        $skuAttribute->getType()->willReturn(AttributeTypes::IDENTIFIER);
        $skuAttribute->getId()->willReturn(1);

        $nameAttribute->getCode()->willReturn('name');
        $nameAttribute->getType()->willReturn(AttributeTypes::TEXT);
        $nameAttribute->getId()->willReturn(2);

        $descAttribute->getCode()->willReturn('description');
        $descAttribute->getType()->willReturn(AttributeTypes::TEXTAREA);
        $descAttribute->getId()->willReturn(3);

        $skuMobileRqrmt->getAttribute()->willReturn($skuAttribute);
        $skuMobileRqrmt->getChannelCode()->willReturn('mobile');
        $skuMobileRqrmt->isRequired()->willReturn(true);

        $skuPrintRqrmt->getAttribute()->willReturn($skuAttribute);
        $skuPrintRqrmt->getChannelCode()->willReturn('print');
        $skuPrintRqrmt->isRequired()->willReturn(true);

        $attributeRepository->getIdentifierCode()->shouldBeCalled()->willReturn('sku');
        $attributeRepository->findOneByIdentifier('name')->willReturn($nameAttribute);
        $attributeRepository->findOneByIdentifier('description')->willReturn($descAttribute);

        $family->updateAttributes([$skuAttribute, $nameAttribute, $descAttribute]);

        $channelRepository->findOneByIdentifier('mobile')->willReturn($mobileChannel);
        $channelRepository->findOneByIdentifier('print')->willReturn($printChannel);

        $mobileChannel->getId()->willReturn(1);
        $mobileChannel->getCode()->willReturn('mobile');
        $printChannel->getId()->willReturn(2);
        $printChannel->getCode()->willReturn('print');

        $attributeRequirementRepo->findOneBy([
            'attribute' => 1,
            'channel' => 1,
            'family' => 42
        ])->willReturn($skuMobileRqrmt);
        $attributeRequirementRepo->findOneBy([
            'attribute' => 2,
            'channel' => 1,
            'family' => 42
        ])->willReturn($nameMobileRqrmt);
        $attributeRequirementRepo->findOneBy([
            'attribute' => 1,
            'channel' => 2,
            'family' => 42
        ])->willReturn($skuPrintRqrmt);
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

        $family->setCode('mycode')->shouldBeCalled();

        $this->update($family, $values, []);
    }

    private function makeLocale(string $code): Locale
    {
        $locale = new Locale();
        $locale->setCode($code);
        return $locale;
    }
}
