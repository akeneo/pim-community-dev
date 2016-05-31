<?php

namespace spec\Pim\Component\Connector\ArrayConverter\Structured;

use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\ArrayConverter\FieldsRequirementChecker;
use Pim\Component\Connector\Exception\ArrayConversionException;
use Prophecy\Argument;

class JobInstanceConverterSpec extends ObjectBehavior
{
    function let(FieldsRequirementChecker $checker)
    {
        $this->beConstructedWith($checker);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Connector\ArrayConverter\Structured\JobInstanceConverter');
    }

    function it_is_a_standard_array_convert()
    {
        $this->shouldImplement('Pim\Component\Connector\ArrayConverter\ArrayConverterInterface');
    }

    function it_converts($checker)
    {
        $fields = [
            'connector' => 'Data fixtures',
            'alias' => 'fixtures_currency_csv',
            'label' => 'Currencies data fixtures',
            'type' => 'type',
            'configuration' => [
                'filePath' => 'currencies.csv',
            ],
            'code' => 'fixtures_currency_csv',
        ];

        $checker->checkFieldsPresence($fields, ['code', 'type', 'connector', 'label', 'alias'])->shouldBeCalled();
        $checker->checkFieldsFilling($fields, ['code', 'type', 'connector', 'label'])->shouldBeCalled();

        $this->convert($fields)->shouldReturn($fields);
    }

    function it_throws_an_exception_if_some_data_are_missing($checker)
    {
        $checker
            ->checkFieldsPresence(['code' => 'Code'], ['code', 'type', 'connector', 'label', 'alias'])
            ->willThrow(new ArrayConversionException('Field "code" must be filled'));

        $this->shouldThrow('Pim\Component\Connector\Exception\ArrayConversionException')
            ->during('convert', [['code' => 'Code']]);
    }

    function it_throws_an_exception_if_some_data_are_empty($checker)
    {
        $checker
            ->checkFieldsPresence(['code' => 'Code'], ['code', 'type', 'connector', 'label', 'alias'])
            ->shouldBeCalled();

        $checker
            ->checkFieldsFilling(['code' => 'Code'], ['code', 'type', 'connector', 'label'])
            ->willThrow(new ArrayConversionException('Field "code" must be filled'));

        $this->shouldThrow('Pim\Component\Connector\Exception\ArrayConversionException')
            ->during('convert', [['code' => 'Code']]);
    }
}
