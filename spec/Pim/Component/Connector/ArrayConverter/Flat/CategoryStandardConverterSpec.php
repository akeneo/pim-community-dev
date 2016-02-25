<?php

namespace spec\Pim\Component\Connector\ArrayConverter\Flat;

use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\ArrayConverter\FieldsRequirementValidator;

class CategoryStandardConverterSpec extends ObjectBehavior
{
    function let(FieldsRequirementValidator $validator)
    {
        $this->beConstructedWith($validator);
    }

    function it_converts()
    {
        $fields = [
            'code'        => 'mycode',
            'parent'      => 'master',
            'label-fr_FR' => 'Ma superbe catégorie',
            'label-en_US' => 'My awesome category',
        ];

        $this->convert($fields)->shouldReturn([
            'labels'   => [
                'fr_FR' => 'Ma superbe catégorie',
                'en_US' => 'My awesome category',
            ],
            'code'     => 'mycode',
            'parent'   => 'master',
        ]);
    }

    function it_throws_an_exception_if_required_fields_are_not_in_array($validator)
    {
        $item = ['not_a_code' => ''];

        $validator
            ->validateFields($item, ['code'])
            ->willThrow(new \LogicException('Field "code" is expected, provided fields are "not_a_code"'));

        $this
            ->shouldThrow(new \LogicException('Field "code" is expected, provided fields are "not_a_code"'))
            ->during('convert', [$item]);
    }

    function it_throws_an_exception_if_required_field_code_is_empty($validator)
    {
        $item = ['parent' => 'master', 'code' => ''];

        $validator
            ->validateFields($item, ['code'])
            ->willThrow(new \LogicException('Field "code" must be filled'));

        $this
            ->shouldThrow(new \LogicException('Field "code" must be filled'))
            ->during('convert', [$item]);
    }

    function it_throws_an_exception_if_required_fields_are_empty($validator)
    {
        $item = ['code' => ''];

        $validator
            ->validateFields($item, ['code'])
            ->willThrow(new \LogicException('Field "code" must be filled'));

        $this
            ->shouldThrow(new \LogicException('Field "code" must be filled'))
            ->during('convert', [$item]);
    }
}
