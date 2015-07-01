<?php

namespace spec\Pim\Component\Connector\ArrayConverter\Flat;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;
use Pim\Component\Connector\ArrayConverter\Flat\ProductStandardConverter;

class GroupStandardConverterSpec extends ObjectBehavior
{
    function let(LocaleRepositoryInterface $localeRepository)
    {
        $this->beConstructedWith($localeRepository);
    }

    function it_converts($localeRepository)
    {
        $fields = [
            'code'        => 'mycode',
            'type'        => 'RELATED',
            'label-fr_FR' => 'T-shirt super beau',
            'label-en_US' => 'T-shirt very beautiful',
        ];

        $localeRepository->getActivatedLocaleCodes()->willReturn(['fr_FR', 'en_US']);

        $this->convert($fields)->shouldReturn([
            'labels'   => [
                'fr_FR' => 'T-shirt super beau',
                'en_US' => 'T-shirt very beautiful',
            ],
            'code'     => 'mycode',
            'type'     => 'RELATED',
        ]);
    }

    function it_throws_an_exception_if_required_fields_are_not_in_array()
    {
        $this->shouldThrow(new \LogicException('Field "code" is expected, provided fields are "not_a_code"'))->during(
            'convert',
            [['not_a_code' => '']]
        );
    }

    function it_throws_an_exception_if_required_field_type_is_not_in_array()
    {
        $this->shouldThrow(new \LogicException('Field "type" is expected, provided fields are "code, not_a_code"'))->during(
            'convert',
            [['code' => 'my-code', 'not_a_code' => '']]
        );
    }

    function it_throws_an_exception_if_required_field_code_is_empty()
    {
        $this->shouldThrow(new \LogicException('Field "code" must be filled'))->during(
            'convert',
            [['type' => 'RELATED', 'code' => '']]
        );
    }

    function it_throws_an_exception_if_required_fields_are_empty()
    {
        $this->shouldThrow(new \LogicException('Field "code" must be filled'))->during(
            'convert',
            [['type' => '', 'code' => '']]
        );
    }

    function it_throws_an_exception_if_required_field_type_is_empty()
    {
        $this->shouldThrow(new \LogicException('Field "type" must be filled'))->during(
            'convert',
            [['code' => 'my-code', 'type' => '']]
        );
    }

    function it_throws_an_exception_if_there_is_not_authorized_fields_in_array($localeRepository)
    {
        $localeRepository->getActivatedLocaleCodes()->willReturn(['fr_FR', 'en_US']);
        $exception = new \LogicException(sprintf(
            'Field "%s" is provided, authorized fields are: "%s"',
            'not_authorized',
            'type, code, label-fr_FR, label-en_US'
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
