<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Processor\Denormalization\ArrayConverter\Structured;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ValidatorInterface;

class AttributeOptionToStandardConverterSpec extends ObjectBehavior
{
    function it_is_a_standard_array_converter()
    {
        $this->shouldImplement(
            'Pim\Bundle\BaseConnectorBundle\Processor\Denormalization\ArrayConverter\StandardArrayConverterInterface'
        );
    }
    
    function it_converts_an_item_to_standard_format()
    {
        $this->convert(
            [
                'labels' => [
                    'de_DE' => '210 x 1219 mm',
                    'en_US' => '210 x 1219 mm',
                    'fr_FR' => '210 x 1219 mm'
                ],
                'attribute'   => 'maximum_print_size',
                'code'        => '210_x_1219_mm',
                'sortOrder'  => 2
            ]
        )->shouldReturn(
            [
                'labels' => [
                    'de_DE' => '210 x 1219 mm',
                    'en_US' => '210 x 1219 mm',
                    'fr_FR' => '210 x 1219 mm'
                ],
                'attribute'   => 'maximum_print_size',
                'code'        => '210_x_1219_mm',
                'sort_order'  => 2
            ]
        );
    }

    function it_throws_exception_when_the_attribute_field_is_missing()
    {
        $this
            ->shouldThrow('Pim\Bundle\BaseConnectorBundle\Exception\ArrayConversionException')
            ->during(
                'convert',
                [
                    [
                        'code'         => '210_x_1219_mm',
                        'sort_order'   => '2',
                        'label-de_DE'  => '210 x 1219 mm',
                        'label-en_US'  => '210 x 1219 mm',
                        'label-fr_FR'  => '210 x 1219 mm',
                    ]
                ]
            );
    }

    function it_throws_exception_when_unauthorized_field_is_provided()
    {
        $this
            ->shouldThrow('Pim\Bundle\BaseConnectorBundle\Exception\ArrayConversionException')
            ->during(
                'convert',
                [
                    [
                        'attribute'    => 'maximum_print_size',
                        'code'         => '210_x_1219_mm',
                        'sort_order'   => '2',
                        'label-de_DE'  => '210 x 1219 mm',
                        'label-en_US'  => '210 x 1219 mm',
                        'label-fr_FR'  => '210 x 1219 mm',
                        'unknow_field' => 'My data'
                    ]
                ]
            );
    }

}
