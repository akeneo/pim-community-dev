<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Category\Connector\ArrayConverter\FlatToStandard;

use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker;

class CategorySpec extends ObjectBehavior
{
    function let(FieldsRequirementChecker $fieldChecker)
    {
        $this->beConstructedWith($fieldChecker);
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
        $item = ['parent' => 'master', 'code' => ''];

        $fieldChecker
            ->checkFieldsPresence($item, ['code'])
            ->willThrow(new \LogicException('Field "code" must be filled'));

        $this
            ->shouldThrow(new \LogicException('Field "code" must be filled'))
            ->during('convert', [$item]);
    }

    function it_throws_an_exception_if_required_fields_are_empty($fieldChecker)
    {
        $item = ['code' => ''];

        $fieldChecker
            ->checkFieldsPresence($item, ['code'])
            ->willThrow(new \LogicException('Field "code" must be filled'));

        $this
            ->shouldThrow(new \LogicException('Field "code" must be filled'))
            ->during('convert', [$item]);
    }
}
