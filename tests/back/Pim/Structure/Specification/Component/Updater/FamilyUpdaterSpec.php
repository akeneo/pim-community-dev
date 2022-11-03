<?php

namespace Specification\Akeneo\Pim\Structure\Component\Updater;

use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\Channel\Infrastructure\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Pim\Structure\Component\Factory\AttributeRequirementFactory;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeRequirementInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRequirementRepositoryInterface;
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
        AttributeRepositoryInterface $attributeRepository,
        ChannelRepositoryInterface $channelRepository,
        AttributeRequirementFactory $attributeRequirementFactory,
        AttributeRequirementRepositoryInterface $attributeRequirementRepo,
        TranslatableUpdater $translatableUpdater,
        IdentifiableObjectRepositoryInterface $localeRepository,
        AttributeInterface $sku,
        AttributeInterface $name,
        AttributeInterface $description,
        AttributeInterface $picture,
        ChannelInterface $mobile,
        ChannelInterface $print,
    ) {
        $attributeRepository->getIdentifierCode()->willReturn('sku');
        $attributeRepository->findOneByIdentifier('sku')->willReturn($sku);
        $attributeRepository->findOneByIdentifier('name')->willReturn($name);
        $attributeRepository->findOneByIdentifier('description')->willReturn($description);
        $attributeRepository->findOneByIdentifier('picture')->willReturn($picture);
        $attributeRepository->findOneByIdentifier(Argument::any())->willReturn(null);
        $channelRepository->findOneByIdentifier('mobile')->willReturn($mobile);
        $channelRepository->findOneByIdentifier('print')->willReturn($print);

        $this->beConstructedWith(
            $attributeRepository,
            $channelRepository,
            $attributeRequirementFactory,
            $attributeRequirementRepo,
            $translatableUpdater,
            $localeRepository
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(FamilyUpdater::class);
    }

    function it_is_an_updater()
    {
        $this->shouldImplement(ObjectUpdaterInterface::class);
    }

    function it_throws_an_exception_when_trying_to_update_anything_but_a_family()
    {
        $this->shouldThrow(
            InvalidObjectException::objectExpected(
                'stdClass',
                FamilyInterface::class
            )
        )->during('update', [new \stdClass(), []]);
    }

    function it_updates_a_family_code(FamilyInterface $family)
    {
        $family->setCode('new_code')->shouldBeCalled();

        $this->update($family, ['code' => 'new_code']);
    }

    function it_updates_the_attributes_of_an_existing_family(
        FamilyInterface $family,
        AttributeInterface $name,
        AttributeInterface $description
    ) {
        $family->getCreated()->willReturn(new \DateTime());
        $family->updateAttributes([$description, $name])->shouldBeCalled();

        $this->update($family, ['attributes' => ['description', 'name']]);
    }

    function it_adds_the_identifier_attribute_for_a_new_family(
        FamilyInterface $family,
        AttributeInterface $name,
        AttributeInterface $description,
        AttributeInterface $sku
    ) {
        $family->getCreated()->willReturn(null);
        $family->updateAttributes([$description, $name, $sku])->shouldBeCalled();

        $this->update($family, ['attributes' => ['description', 'name']]);
    }

    function it_throws_an_exception_if_attribute_does_not_exist(FamilyInterface $family)
    {
        $family->getCreated()->willReturn(new \DateTime());

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'attributes',
                'code',
                'The attribute does not exist',
                FamilyUpdater::class,
                'unknown'
            )
        )->during('update', [$family, ['attributes' => ['unknown']]]);
    }

    function it_updates_the_attribute_as_label(FamilyInterface $family, AttributeInterface $name)
    {
        $family->setAttributeAsLabel($name)->shouldBeCalled();

        $this->update($family, ['attribute_as_label' => 'name']);
    }

    function it_throws_an_exception_if_the_attribute_as_label_is_null_or_does_not_exist(
        FamilyInterface $family,
    ) {
        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'attribute_as_label',
                'code',
                'The attribute does not exist',
                FamilyUpdater::class,
                null
            )
        )->during('update', [$family, ['attribute_as_label' => null]]);

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'attribute_as_label',
                'code',
                'The attribute does not exist',
                FamilyUpdater::class,
                'unknown'
            )
        )->during('update', [$family, ['attribute_as_label' => 'unknown']]);
    }

    function it_updates_the_attribute_as_image(FamilyInterface $family, AttributeInterface $picture)
    {
        $family->setAttributeAsImage($picture)->shouldBeCalled();

        $this->update($family, ['attribute_as_image' => 'picture']);
    }

    function it_removes_the_attribute_as_image(FamilyInterface $family)
    {
        $family->setAttributeAsImage(null)->shouldBeCalled();

        $this->update($family, ['attribute_as_image' => '']);
    }

    function it_throws_an_exception_if_the_attribute_as_image_does_not_exist(FamilyInterface $family)
    {
        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'attribute_as_image',
                'code',
                'The attribute does not exist',
                FamilyUpdater::class,
                'unknown'
            )
        )->during('update', [$family, ['attribute_as_image' => 'unknown']]);
    }

    function it_updates_the_family_labels(
        IdentifiableObjectRepositoryInterface $localeRepository,
        TranslatableUpdater $translatableUpdater,
        FamilyInterface $family,
        LocaleInterface $enUS,
        LocaleInterface $frFR
    ) {
        $enUS->getCode()->willReturn('en_US');
        $localeRepository->findOneByIdentifier('EN_US')->willReturn($enUS);
        $frFR->getCode()->willReturn('fr_FR');
        $localeRepository->findOneByIdentifier('fr_FR')->willReturn($frFR);
        $localeRepository->findOneByIdentifier('unknown')->willReturn(null);

        $translatableUpdater->update(
            $family,
            [
                'en_US' => 'The family',
                'fr_FR' => 'La famille',
                'unknown' => 'Other label',
            ]
        )->shouldBeCalled();

        $this->update($family, [
            'labels' => [
                'EN_US' => 'The family',
                'fr_FR' => 'La famille',
                'unknown' => 'Other label',
            ],
        ]);
    }

    function it_updates_the_attribute_requirements_of_an_existing_family(
        AttributeRequirementFactory $attributeRequirementFactory,
        AttributeRequirementRepositoryInterface $attributeRequirementRepo,
        AttributeInterface $name,
        AttributeInterface $description,
        ChannelInterface $mobile,
        ChannelInterface $print,
        FamilyInterface $family,
        AttributeRequirementInterface $skuMobileRqt,
        AttributeRequirementInterface $nameMobileRqt,
        AttributeRequirementInterface $skuPrintRqt,
        AttributeRequirementInterface $namePrintRqt,
        AttributeRequirementInterface $descriptionPrintRqt,
    ) {
        $family->getCreated()->willReturn(new \DateTime());
        $family->getAttributeRequirements()->willReturn([
            'sku_mobile' => $skuMobileRqt,
            'sku_print' => $skuPrintRqt,
            'name_print' => $namePrintRqt,
        ]);
        $skuMobileRqt->getChannelCode()->willReturn('mobile');
        $skuMobileRqt->getAttributeCode()->willReturn('sku');
        $skuPrintRqt->getChannelCode()->willReturn('print');
        $skuPrintRqt->getAttributeCode()->willReturn('sku');
        $namePrintRqt->getChannelCode()->willReturn('print');
        $namePrintRqt->getAttributeCode()->willReturn('name');
        $namePrintRqt->isRequired()->willReturn(true);

        $family->removeAttributeRequirement($skuMobileRqt)->shouldBeCalled();
        $family->removeAttributeRequirement($skuPrintRqt)->shouldBeCalled();
        $attributeRequirementRepo->findOneBy(['attribute' => $name, 'channel' => $mobile, 'family' => $family])
                                 ->shouldBeCalled()->willReturn(null);
        $attributeRequirementFactory->createAttributeRequirement($name, $mobile, true)->shouldBeCalled()->willReturn(
            $nameMobileRqt
        );
        $family->addAttributeRequirement($nameMobileRqt)->shouldBeCalled();
        $attributeRequirementRepo->findOneBy(['attribute' => $description, 'channel' => $print, 'family' => $family])
                                 ->shouldBeCalled()->willReturn(null);
        $attributeRequirementFactory->createAttributeRequirement($description, $print, true)->shouldBeCalled()
                                    ->willReturn($descriptionPrintRqt);
        $family->addAttributeRequirement($descriptionPrintRqt)->shouldBeCalled();

        $this->update($family, [
            'attribute_requirements' => [
                'mobile' => ['name'],
                'print' => ['description', 'name'],
            ],
        ]);
    }

    function it_adds_the_identifier_in_the_requirements_of_a_new_family(
        AttributeRequirementFactory $attributeRequirementFactory,
        AttributeRequirementRepositoryInterface $attributeRequirementRepo,
        AttributeInterface $sku,
        AttributeInterface $name,
        ChannelInterface $mobile,
        ChannelInterface $print,
        FamilyInterface $family,
        AttributeRequirementInterface $skuMobileRqt,
        AttributeRequirementInterface $nameMobileRqt,
        AttributeRequirementInterface $skuPrintRqt,
    ) {
        $family->getCreated()->willReturn(null);
        $family->getAttributeRequirements()->willReturn([
            'sku_mobile' => $skuMobileRqt,
        ]);

        $skuMobileRqt->getChannelCode()->willReturn('mobile');
        $skuMobileRqt->getAttributeCode()->willReturn('sku');
        $skuMobileRqt->isRequired()->willReturn(true);
        $skuPrintRqt->getChannelCode()->willReturn('print');
        $skuPrintRqt->getAttributeCode()->willReturn('sku');

        $attributeRequirementRepo->findOneBy(Argument::any())->willReturn(null);
        $attributeRequirementFactory->createAttributeRequirement($name, $mobile, true)
                                    ->shouldBeCalled()->willReturn($nameMobileRqt);
        $nameMobileRqt->setRequired(true)->shouldBeCalled();
        $family->addAttributeRequirement($nameMobileRqt)->shouldBeCalled();
        $attributeRequirementFactory->createAttributeRequirement($sku, $print, true)
                                    ->shouldBeCalled()->willReturn($skuPrintRqt);
        $skuPrintRqt->setRequired(true)->shouldBeCalled();
        $family->addAttributeRequirement($skuPrintRqt)->shouldBeCalled();
        $family->removeAttributeRequirement($skuMobileRqt)->shouldNotBeCalled();

        $this->update(
            $family,
            [
                'attribute_requirements' => [
                    'mobile' => ['name'],
                    'print' => [],
                ],
            ]
        );
    }

    function it_throws_an_exception_if_a_channel_does_not_exist(
        FamilyInterface $family,
        ChannelRepositoryInterface $channelRepository
    ) {
        $family->getCreated()->willReturn(new \DateTime());
        $family->getAttributeRequirements()->willReturn([]);

        $channelRepository->findOneByIdentifier('ecommerce')->willReturn(null);

        $data = [
            'attribute_requirements' => [
                'ecommerce' => ['name'],
            ],
        ];
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

    function it_does_not_remove_requirements_when_no_requirements_are_provided(
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
        FamilyInterface $family,
        AttributeInterface $skuAttribute,
        AttributeRequirementInterface $skuMobileRqt,
        AttributeRequirementInterface $skuPrintRqt,
    ) {
        $family->getCreated()->willReturn(new \DateTime());
        $family->getAttributeRequirements()->willReturn([
            'sku_mobile' => $skuMobileRqt,
            'sku_print' => $skuPrintRqt,
        ]);

        $skuMobileRqt->getChannelCode()->willReturn('mobile');
        $skuMobileRqt->getAttributeCode()->willReturn('sku');
        $skuMobileRqt->isRequired()->willReturn(true);

        $family->removeAttributeRequirement(Argument::any())->shouldNotBeCalled();

        $this->update($family, [
            'attribute_requirements' => [
                'mobile' => ['sku'],
            ]
        ]);
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
}
