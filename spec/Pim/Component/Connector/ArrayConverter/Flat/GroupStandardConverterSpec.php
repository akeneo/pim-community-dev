<?php

namespace spec\Pim\Component\Connector\ArrayConverter\Flat;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Pim\Component\Connector\ArrayConverter\FieldsRequirementValidator;
use Pim\Component\Connector\ArrayConverter\Flat\ProductStandardConverter;

class GroupStandardConverterSpec extends ObjectBehavior
{
    function let(LocaleRepositoryInterface $localeRepository, FieldsRequirementValidator $validator)
    {
        $this->beConstructedWith($localeRepository, $validator);
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

    function it_throws_an_exception_if_required_fields_are_not_in_array($validator)
    {
        $item = ['not_a_code' => ''];

        $validator
            ->validateFields($item, ['code', 'type'])
            ->willThrow(new \LogicException('Field "code" is expected, provided fields are "not_a_code"'));

        $this
            ->shouldThrow(new \LogicException('Field "code" is expected, provided fields are "not_a_code"'))
            ->during('convert', [$item]);
    }

    function it_throws_an_exception_if_required_field_type_is_not_in_array($validator)
    {
        $item = ['code' => 'my-code', 'not_a_code' => ''];

        $validator
            ->validateFields($item, ['code', 'type'])
            ->willThrow(new \LogicException('Field "type" is expected, provided fields are "code, not_a_code"'));

        $this
            ->shouldThrow(new \LogicException('Field "type" is expected, provided fields are "code, not_a_code"'))
            ->during('convert', [$item]);
    }

    function it_throws_an_exception_if_required_field_code_is_empty($validator)
    {
        $item = ['type' => 'RELATED', 'code' => ''];

        $validator
            ->validateFields($item, ['code', 'type'])
            ->willThrow(new \LogicException('Field "code" must be filled'));

        $this
            ->shouldThrow(new \LogicException('Field "code" must be filled'))
            ->during('convert', [$item]);
    }

    function it_throws_an_exception_if_required_fields_are_empty($validator)
    {
        $item = ['type' => '', 'code' => ''];

        $validator
            ->validateFields($item, ['code', 'type'])
            ->willThrow(new \LogicException('Field "code" must be filled'));

        $this
            ->shouldThrow(new \LogicException('Field "code" must be filled'))
            ->during('convert', [$item]);
    }

    function it_throws_an_exception_if_required_field_type_is_empty($validator)
    {
        $item = ['code' => 'my-code', 'type' => ''];

        $validator
            ->validateFields($item, ['code', 'type'])
            ->willThrow(new \LogicException('Field "type" must be filled'));

        $this
            ->shouldThrow(new \LogicException('Field "type" must be filled'))
            ->during('convert', [$item]);
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
