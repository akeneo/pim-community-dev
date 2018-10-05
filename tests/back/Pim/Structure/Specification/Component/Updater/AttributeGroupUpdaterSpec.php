<?php

namespace Specification\Akeneo\Pim\Structure\Component\Updater;

use Akeneo\Tool\Component\Localization\TranslatableUpdater;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeGroupRepositoryInterface;
use Akeneo\Pim\Structure\Component\Updater\AttributeGroupUpdater;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;

class AttributeGroupUpdaterSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        AttributeGroupRepositoryInterface $attributeGroupRepository,
        TranslatableUpdater $translatableUpdater
    ) {
        $this->beConstructedWith(
            $attributeRepository,
            $attributeGroupRepository,
            $translatableUpdater
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeGroupUpdater::class);
    }

    function it_is_an_updater()
    {
        $this->shouldImplement(ObjectUpdaterInterface::class);
    }

    function it_throw_an_exception_when_trying_to_update_anything_else_than_an_attribute_group()
    {
        $this->shouldThrow(
            InvalidObjectException::objectExpected(
                'stdClass',
                AttributeGroupInterface::class
            )
        )->during(
            'update',
            [new \stdClass(), []]
        );
    }

    function it_updates_an_attribute_group(
        $attributeRepository,
        $attributeGroupRepository,
        $translatableUpdater,
        AttributeGroupInterface $attributeGroup,
        AttributeGroupInterface $defaultGroup,
        AttributeInterface $size,
        AttributeInterface $mainColor,
        AttributeInterface $sku
    ) {
        $values = [
            'code'       => 'sizes',
            'sort_order' => 1,
            'attributes' => ['size', 'main_color'],
            'labels'     => [
                'en_US' => 'Sizes',
                'fr_FR' => 'Tailles'
            ]
        ];

        $attributeGroup->getCode()->willReturn('sizes');

        $attributeGroup->setCode('sizes')->shouldBeCalled();
        $attributeGroup->setSortOrder(1)->shouldBeCalled();

        $sku->getCode()->willReturn('sku');
        $size->getCode()->willReturn('size');
        $attributeGroup->getAttributes()->willReturn([$sku, $size]);
        $attributeGroupRepository->findDefaultAttributeGroup()->willReturn($defaultGroup);

        $defaultGroup->addAttribute($sku)->shouldBeCalled();

        $attributeRepository->findOneByIdentifier('size')->willReturn($size);
        $attributeRepository->findOneByIdentifier('main_color')->willReturn($mainColor);
        $attributeGroup->addAttribute($size)->shouldBeCalled();
        $attributeGroup->addAttribute($mainColor)->shouldBeCalled();

        $translatableUpdater->update($attributeGroup, $values['labels'])->shouldBeCalled();

        $this->update($attributeGroup, $values, []);
    }

    function it_throws_an_exception_if_attribute_not_found(
        $attributeRepository,
        AttributeGroupInterface $attributeGroup
    ) {
        $values = [
            'attributes' => ['foo'],
        ];

        $attributeGroup->getCode()->willReturn('sizes');

        $attributeGroup->getAttributes()->willReturn([]);

        $attributeRepository->findOneByIdentifier('foo')->willReturn(null);

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'attributes',
                'attribute code',
                'The attribute does not exist',
                AttributeGroupUpdater::class,
                'foo'
            ))
            ->during('update', [$attributeGroup, $values]);
    }

    function it_does_not_update_attributes_from_the_default_group(
        $attributeGroupRepository,
        $translatableUpdater,
        AttributeGroupInterface $attributeGroup
    ) {
        $values = [
            'code' => 'other',
            'sort_order' => 1,
            'attributes' => ['foo'],
            'labels' => [
                'en_US' => 'Other',
                'fr_FR' => 'Autre'
            ]
        ];

        $attributeGroup->getCode()->willReturn('other');

        $attributeGroup->setCode('other')->shouldBeCalled();
        $attributeGroup->setSortOrder(1)->shouldBeCalled();

        $translatableUpdater->update($attributeGroup, $values['labels'])->shouldBeCalled();

        $attributeGroupRepository->findDefaultAttributeGroup()->shouldNotBeCalled();
        $attributeGroup->getAttributes()->shouldNotBeCalled();

        $this->update($attributeGroup, $values, []);
    }

    function it_throws_an_exception_when_labels_is_not_an_array(AttributeGroupInterface $attributeGroup)
    {
        $data = [
            'labels' => 'foo',
        ];

        $this
            ->shouldThrow(
                InvalidPropertyTypeException::arrayExpected(
                    'labels',
                    AttributeGroupUpdater::class,
                    'foo'
                )
            )
            ->during('update', [$attributeGroup, $data, []]);
    }

    function it_throws_an_exception_when_a_value_in_labels_array_is_not_a_scalar(AttributeGroupInterface $attributeGroup)
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
                    'one of the "labels" values is not a scalar',
                    AttributeGroupUpdater::class,
                    ['en_US' => 'us_Label', 'fr_FR' => []]
                )
            )
            ->during('update', [$attributeGroup, $data, []]);
    }

    function it_throws_an_exception_when_attributes_is_not_an_array(AttributeGroupInterface $attributeGroup)
    {
        $data = [
            'attributes' => 'foo',
        ];

        $this
            ->shouldThrow(
                InvalidPropertyTypeException::arrayExpected(
                    'attributes',
                    AttributeGroupUpdater::class,
                    'foo'
                )
            )
            ->during('update', [$attributeGroup, $data, []]);
    }

    function it_throws_an_exception_when_a_value_in_attributes_array_is_not_a_scalar(AttributeGroupInterface $attributeGroup)
    {
        $data = [
            'attributes' => ['foo', []],
        ];

        $this
            ->shouldThrow(
                InvalidPropertyTypeException::validArrayStructureExpected(
                    'attributes',
                    'one of the "attributes" values is not a scalar',
                    AttributeGroupUpdater::class,
                    ['foo', []]
                )
            )
            ->during('update', [$attributeGroup, $data, []]);
    }

    function it_throws_an_exception_when_code_is_not_a_scalar(AttributeGroupInterface $attributeGroup)
    {
        $data = [
            'code' => [],
        ];

        $this
            ->shouldThrow(
                InvalidPropertyTypeException::scalarExpected(
                    'code',
                    AttributeGroupUpdater::class,
                    []
                )
            )
            ->during('update', [$attributeGroup, $data, []]);
    }

    function it_throws_an_exception_when_sort_order_is_not_a_scalar(AttributeGroupInterface $attributeGroup)
    {
        $data = [
            'sort_order' => [],
        ];

        $this
            ->shouldThrow(
                InvalidPropertyTypeException::scalarExpected(
                    'sort_order',
                    AttributeGroupUpdater::class,
                    []
                )
            )
            ->during('update', [$attributeGroup, $data, []]);
    }

    function it_throws_an_exception_when_trying_to_update_a_non_existent_field(AttributeGroupInterface $attributeGroup)
    {
        $data = [
            'unknown_field' => 'field',
        ];

        $this->shouldThrow(
            UnknownPropertyException::unknownProperty(
                'unknown_field',
                new NoSuchPropertyException()
            )
        )->during('update', [$attributeGroup, $data, []]);
    }
}
