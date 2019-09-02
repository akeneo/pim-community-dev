<?php

namespace spec\Akeneo\Tool\Bundle\MeasureBundle\Convert;

use Akeneo\Tool\Bundle\MeasureBundle\Convert\MeasureConverter;
use Akeneo\Tool\Bundle\MeasureBundle\Exception\UnknownFamilyMeasureException;
use Akeneo\Tool\Bundle\MeasureBundle\Exception\UnknownMeasureException;
use Akeneo\Tool\Bundle\MeasureBundle\Family\WeightFamilyInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Yaml\Yaml;

class MeasureConverterSpec extends ObjectBehavior
{
    function let()
    {
        $filename = realpath(dirname(__FILE__) .'/../Resources/config/measure-test.yml');
        if (!file_exists($filename)) {
            throw new \Exception(sprintf('Config file "%s" does not exist', $filename));
        }

        $config = Yaml::parse(file_get_contents($filename));
        $this->beConstructedWith($config);
    }

    public function it_allows_to_define_the_family()
    {
        $this->setFamily('Length')->shouldReturnAnInstanceOf(MeasureConverter::class);
    }

    public function it_throws_an_exception_if_an_unknown_family_is_set()
    {
        $this
            ->shouldThrow(
                new UnknownFamilyMeasureException()
            )
            ->during('setFamily', ['foo']);
    }

    public function it_converts_a_value_from_a_base_unit_to_a_final_unit()
    {
        $this->setFamily('Weight');
        $this->convert(
            WeightFamilyInterface::KILOGRAM,
            WeightFamilyInterface::MILLIGRAM,
            1
        )->shouldReturn('1000000.000000000000');
    }

    public function it_converts_a_value_to_a_standard_unit()
    {
        $this->setFamily('Weight');
        $this->convertBaseToStandard(
            WeightFamilyInterface::MILLIGRAM,
            1000
        )->shouldReturn('1.000000000000');
    }

    public function it_converts_a_standard_value_to_a_final_unit()
    {
        $this->setFamily('Weight');
        $this->convertStandardToResult(
            WeightFamilyInterface::KILOGRAM,
            10
        )->shouldReturn('0.010000000000');
    }

    public function it_throws_an_exception_if_the_unit_measure_does_not_exist()
    {
        $this->setFamily('Weight');
        $this
            ->shouldThrow(
                new UnknownMeasureException(
                    'Could not find metric unit "foo" in family "Weight"'
                )
            )
            ->during('convertBaseToStandard', ['foo', Argument::any()]);

        $this
            ->shouldThrow(
                new UnknownMeasureException(
                    'Could not find metric unit "foo" in family "Weight"'
                )
            )
            ->during('convertStandardToResult', ['foo', Argument::any()]);
    }
}
