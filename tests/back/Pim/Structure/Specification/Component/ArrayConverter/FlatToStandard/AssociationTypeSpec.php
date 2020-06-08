<?php

namespace Specification\Akeneo\Pim\Structure\Component\ArrayConverter\FlatToStandard;

use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker;

class AssociationTypeSpec extends ObjectBehavior
{
    function let(FieldsRequirementChecker $fieldChecker)
    {
        $this->beConstructedWith($fieldChecker);
    }

    function it_converts()
    {
        $fields = [
            'code'        => 'mycode',
            'label-fr_FR' => 'Vente croisée',
            'label-en_US' => 'Cross sell',
            'is_two_way' => 1,
            'is_quantified' => 0,
        ];

        $this->convert($fields)->shouldReturn(
            [
                'labels' => [
                    'fr_FR' => 'Vente croisée',
                    'en_US' => 'Cross sell',
                ],
                'code'   => 'mycode',
                'is_two_way' => true,
                'is_quantified' => false,
            ]
        );
    }

    function it_converts_two_way_and_quantified_to_false_by_default()
    {
        $fields = [
            'code' => 'mycode',
            'label-fr_FR' => 'Vente croisée',
            'label-en_US' => 'Cross sell',
        ];

        $this->convert($fields)->shouldReturn(
            [
                'labels' => [
                    'fr_FR' => 'Vente croisée',
                    'en_US' => 'Cross sell',
                ],
                'code' => 'mycode',
                'is_two_way' => false,
                'is_quantified' => false,
            ]
        );
    }

    function it_converts_to_is_quantified_when_quantified()
    {
        $fields = [
            'code' => 'mycode',
            'label-fr_FR' => 'Vente croisée',
            'label-en_US' => 'Cross sell',
            'is_quantified' => 1,
        ];

        $this->convert($fields)->shouldReturn(
            [
                'labels' => [
                    'fr_FR' => 'Vente croisée',
                    'en_US' => 'Cross sell',
                ],
                'code' => 'mycode',
                'is_two_way' => false,
                'is_quantified' => true,
            ]
        );
    }

    function it_throws_an_exception_if_required_fields_are_not_in_array($fieldChecker)
    {
        $item = ['not_a_code' => ''];

        $fieldChecker
            ->checkFieldsPresence($item, ['code'])
            ->willThrow(new \LogicException('Field "code" is expected, provided fields are "not_a_code"'));

        $this
            ->shouldThrow(new \LogicException('Field "code" is expected, provided fields are "not_a_code"'))
            ->during('convert', [$item]);
    }

    function it_throws_an_exception_if_required_field_code_is_empty($fieldChecker)
    {
        $item = ['code' => ''];

        $fieldChecker
            ->checkFieldsPresence($item, ['code'])
            ->willThrow(new \LogicException('Field "code" must be filled'));

        $this
            ->shouldThrow(new \LogicException('Field "code" must be filled'))
            ->during('convert', [['code' => '']]);
    }
}
