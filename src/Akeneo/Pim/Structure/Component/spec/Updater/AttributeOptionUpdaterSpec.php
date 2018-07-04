<?php

namespace spec\Akeneo\Pim\Structure\Component\Updater;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionValueInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Prophecy\Argument;

class AttributeOptionUpdaterSpec extends ObjectBehavior
{
    function let(AttributeRepositoryInterface $attributeRepository)
    {
        $this->beConstructedWith($attributeRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Akeneo\Pim\Structure\Component\Updater\AttributeOptionUpdater');
    }

    function it_is_a_updater()
    {
        $this->shouldImplement('Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface');
    }

    function it_throw_an_exception_when_trying_to_update_anything_else_than_an_attribute_option()
    {
        $this->shouldThrow(
            InvalidObjectException::objectExpected(
                'stdClass',
                'Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface'
            )
        )->during(
            'update',
            [new \stdClass(), []]
        );
    }

    function it_updates_all_fields_on_a_new_attribute_option(
        $attributeRepository,
        AttributeOptionInterface $attributeOption,
        AttributeInterface $attribute,
        AttributeOptionValueInterface $attributeOptionValue
    ) {
        $attributeOption->getId()->willReturn(null);
        $attributeOption->getAttribute()->willReturn(null);

        $attributeOption->setCode('mycode')->shouldBeCalled();
        $attributeRepository->findOneByIdentifier('myattribute')->willReturn($attribute);
        $attributeOption->setAttribute($attribute)->shouldBeCalled();
        $attributeOption->setSortOrder(12)->shouldBeCalled();
        $attributeOption->setLocale('de_DE')->shouldBeCalled();
        $attributeOption->getTranslation()->willReturn($attributeOptionValue);
        $attributeOptionValue->setLabel('210 x 1219 mm')->shouldBeCalled();

        $this->update(
            $attributeOption,
            [
                'code' => 'mycode',
                'attribute' => 'myattribute',
                'sort_order' => 12,
                'labels' => [
                    'de_DE' => '210 x 1219 mm'
                ]
            ]
        );
    }

    function it_removes_the_translation_when_the_new_label_is_empty(
        AttributeOptionInterface $attributeOption,
        AttributeOptionValueInterface $attributeOptionValue
    ) {
        $attributeOption->setLocale('fr_FR')->shouldBeCalled();
        $attributeOption->getTranslation()->willReturn($attributeOptionValue);
        $attributeOptionValue->setLabel('')->shouldNotBeCalled();
        $attributeOption->removeOptionValue($attributeOptionValue)->shouldBeCalled();

        $this->update(
            $attributeOption,
            [
                'labels' => [
                    'fr_FR' => '',
                ]
            ]
        );
    }

    function it_removes_the_translation_when_the_new_label_is_null(
        AttributeOptionInterface $attributeOption,
        AttributeOptionValueInterface $attributeOptionValue
    ) {
        $attributeOption->setLocale('fr_FR')->shouldBeCalled();
        $attributeOption->getTranslation()->willReturn($attributeOptionValue);
        $attributeOptionValue->setLabel(null)->shouldNotBeCalled();
        $attributeOption->removeOptionValue($attributeOptionValue)->shouldBeCalled();

        $this->update(
            $attributeOption,
            [
                'labels' => [
                    'fr_FR' => null,
                ]
            ]
        );
    }

    function it_throws_an_exception_when_attribute_does_not_exist(
        $attributeRepository,
        AttributeOptionInterface $attributeOption
    ) {
        $attributeOption->getId()->willReturn(null);
        $attributeOption->getAttribute()->willReturn(null);

        $attributeOption->setCode('mycode')->shouldBeCalled();
        $attributeRepository->findOneByIdentifier('myattribute')->willReturn(null);

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'attribute',
                'attribute code',
                'The attribute does not exist',
                'Akeneo\Pim\Structure\Component\Updater\AttributeOptionUpdater',
                'myattribute'
            )
        )->during(
            'update',
            [
                $attributeOption,
                [
                    'code' => 'mycode',
                    'attribute' => 'myattribute',
                    'sort_order' => 12,
                    'labels' => [
                        'de_DE' => '210 x 1219 mm'
                    ]
                ]
            ]
        );
    }

    function it_throws_an_exception_when_code_is_not_scalar(AttributeOptionInterface $attributeOption)
    {
        $values = [
            'code' => [],
        ];

        $this
            ->shouldThrow(
                InvalidPropertyTypeException::scalarExpected(
                    'code',
                    'Akeneo\Pim\Structure\Component\Updater\AttributeOptionUpdater',
                    []
                )
            )
            ->during('update', [$attributeOption, $values, []]);
    }

    function it_throws_an_exception_when_labels_is_not_an_array(AttributeOptionInterface $attributeOption)
    {
        $values = [
            'labels' => 'not_an_array',
        ];

        $this
            ->shouldThrow(
                InvalidPropertyTypeException::arrayExpected(
                    'labels',
                    'Akeneo\Pim\Structure\Component\Updater\AttributeOptionUpdater',
                    'not_an_array'
                )
            )
            ->during('update', [$attributeOption, $values, []]);
    }
}
