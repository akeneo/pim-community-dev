<?php

namespace spec\Pim\Component\Catalog\Updater;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\Catalog\Model\AttributeOptionValueInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Prophecy\Argument;

class AttributeOptionUpdaterSpec extends ObjectBehavior
{
    function let(AttributeRepositoryInterface $attributeRepository)
    {
        $this->beConstructedWith($attributeRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Updater\AttributeOptionUpdater');
    }

    function it_is_a_updater()
    {
        $this->shouldImplement('Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface');
    }

    function it_throw_an_exception_when_trying_to_update_anything_else_than_an_attribute_option()
    {
        $this->shouldThrow(
            InvalidObjectException::objectExpected(
                'stdClass',
                'Pim\Component\Catalog\Model\AttributeOptionInterface'
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

    function it_does_not_update_empty_labels(
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

        $attributeOption->setLocale('de_DE')->shouldNotBeCalled();
        $attributeOption->setLocale('fr_FR')->shouldNotBeCalled();
        $attributeOption->getTranslation()->shouldNotBeCalled();
        $attributeOptionValue->setLabel(null)->shouldNotBeCalled();
        $attributeOptionValue->setLabel('')->shouldNotBeCalled();

        $this->update(
            $attributeOption,
            [
                'code' => 'mycode',
                'attribute' => 'myattribute',
                'labels' => [
                    'de_DE' => null,
                    'fr_FR' => '',
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
                'Pim\Component\Catalog\Updater\AttributeOptionUpdater',
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
                    'Pim\Component\Catalog\Updater\AttributeOptionUpdater',
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
                    'Pim\Component\Catalog\Updater\AttributeOptionUpdater',
                    'not_an_array'
                )
            )
            ->during('update', [$attributeOption, $values, []]);
    }
}
