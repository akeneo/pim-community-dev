<?php

namespace spec\Pim\Component\Connector\ArrayConverter\FlatToStandard;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Pim\Component\Connector\ArrayConverter\FieldsRequirementChecker;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product;

class VariantGroupStandardConverterSpec extends ObjectBehavior
{
    function let(
        LocaleRepositoryInterface $localeRepository,
        AttributeRepositoryInterface $attributeRepository,
        Product $productConverter,
        FieldsRequirementChecker $fieldChecker
    ) {
        $this->beConstructedWith(
            $localeRepository,
            $attributeRepository,
            $productConverter,
            $fieldChecker
        );
    }

    function it_converts(
        $localeRepository,
        $attributeRepository,
        $productConverter,
        AttributeInterface $mainColorAttribute,
        AttributeInterface $tshirtStyleAttribute,
        AttributeInterface $description1Attribute,
        AttributeInterface $description2Attribute
    ) {
        $fields = [
            'code'        => 'mycode',
            'axis'        => 'main_color,secondary_color',
            'type'        => 'VARIANT',
            'label-fr_FR' => 'T-shirt super beau',
            'label-en_US' => 'T-shirt very beautiful',
        ];

        $values = [
            'main_color'                 => 'white',
            'tshirt_style'               => 'turtleneck,sportwear',
            'description-fr_FR-ecommerce'=> '<p>description</p>',
            'description-en_US-ecommerce'=> '<p>description</p>'
        ];

        $convertedValues = [
            'main_color'   => 'white',
            'tshirt_style' => ['turtleneck', 'sportwear'],
            'description'  => [
                [
                    'locale' => 'fr_FR',
                    'scope'  => 'ecommerce',
                    'data'   => '<p>description</p>'
                ],
                [
                    'locale' => 'en_US',
                    'scope'  => 'ecommerce',
                    'data'   => '<p>description</p>'
                ],
            ]
        ];

        $localeRepository->getActivatedLocaleCodes()->willReturn(['fr_FR', 'en_US']);
        $attributeRepository->getIdentifierCode('main_color')->willReturn($mainColorAttribute);
        $attributeRepository->getIdentifierCode('tshirt_style')->willReturn($tshirtStyleAttribute);
        $attributeRepository->getIdentifierCode('description-fr_FR-ecommerce')->willReturn($description1Attribute);
        $attributeRepository->getIdentifierCode('description-en_US-ecommerce')->willReturn($description2Attribute);

        $productConverter->convert($values, ["with_required_identifier" => false])->willReturn($convertedValues);

        $this->convert($fields + $values)->shouldReturn([
            'labels'   => [
                'fr_FR' => 'T-shirt super beau',
                'en_US' => 'T-shirt very beautiful',
            ],
            'code'    => 'mycode',
            'axis'    => ['main_color', 'secondary_color'],
            'type'    => 'VARIANT',
            'values'  => $convertedValues
        ]);
    }

    function it_throws_an_exception_if_required_fields_are_not_in_array($fieldChecker)
    {
        $item = ['not_a_code' => ''];

        $fieldChecker
            ->checkFieldsPresence($item, ['code', 'type'])
            ->willThrow(new \LogicException('Field "code" is expected, provided fields are "not_a_code"'));

        $this
            ->shouldThrow(new \LogicException('Field "code" is expected, provided fields are "not_a_code"'))
            ->during('convert', [$item]);
    }

    function it_throws_an_exception_if_required_field_type_is_not_in_array($fieldChecker)
    {
        $item = ['code' => 'my-code', 'not_a_code' => ''];

        $fieldChecker
            ->checkFieldsPresence($item, ['code', 'type'])
            ->willThrow(new \LogicException('Field "type" is expected, provided fields are "code, not_a_code"'));

        $this
            ->shouldThrow(new \LogicException('Field "type" is expected, provided fields are "code, not_a_code"'))
            ->during('convert', [$item]);
    }

    function it_throws_an_exception_if_required_field_code_is_empty($fieldChecker)
    {
        $item = ['type' => 'RELATED', 'code' => ''];

        $fieldChecker
            ->checkFieldsPresence($item, ['code', 'type'])
            ->willThrow(new \LogicException('Field "code" must be filled'));

        $this
            ->shouldThrow(new \LogicException('Field "code" must be filled'))
            ->during('convert', [$item]);
    }

    function it_throws_an_exception_if_required_fields_are_empty($fieldChecker)
    {
        $item = ['type' => '', 'code' => ''];

        $fieldChecker
            ->checkFieldsPresence($item, ['code', 'type'])
            ->willThrow(new \LogicException('Field "code" must be filled'));

        $this
            ->shouldThrow(new \LogicException('Field "code" must be filled'))
            ->during('convert', [$item]);
    }

    function it_throws_an_exception_if_required_field_type_is_empty($fieldChecker)
    {
        $item = ['code' => 'my-code', 'type' => ''];

        $fieldChecker
            ->checkFieldsPresence($item, ['code', 'type'])
            ->willThrow(new \LogicException('Field "type" must be filled'));

        $this
            ->shouldThrow(new \LogicException('Field "type" must be filled'))
            ->during('convert', [$item]);
    }

    function it_throws_an_exception_if_there_is_non_authorized_fields_in_array($localeRepository)
    {
        $localeRepository->getActivatedLocaleCodes()->willReturn(['fr_FR', 'en_US']);
        $exception = new \LogicException(sprintf(
            'Field "%s" is provided, authorized fields are: "%s"',
            'not_authorized',
            'axis, type, code, label-fr_FR, label-en_US'
        ));

        $this->shouldThrow($exception)->during(
            'convert',
            [
                [
                    'code'           => 'my-code',
                    'type'           => 'RELATED',
                    'not_authorized' => ''
                ]
            ]
        );
    }
}
