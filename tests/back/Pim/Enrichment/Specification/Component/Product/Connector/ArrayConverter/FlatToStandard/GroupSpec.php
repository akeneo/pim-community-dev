<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard;

use PhpSpec\ObjectBehavior;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker;

class GroupSpec extends ObjectBehavior
{
    function let(LocaleRepositoryInterface $localeRepository, FieldsRequirementChecker $fieldChecker)
    {
        $this->beConstructedWith($localeRepository, $fieldChecker);
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
            'labels' => [
                'fr_FR' => 'T-shirt super beau',
                'en_US' => 'T-shirt very beautiful',
            ],
            'code'   => 'mycode',
            'type'   => 'RELATED',
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
