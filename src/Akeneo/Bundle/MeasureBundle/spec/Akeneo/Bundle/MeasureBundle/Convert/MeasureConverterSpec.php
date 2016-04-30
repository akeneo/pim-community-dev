<?php

namespace spec\Akeneo\Bundle\MeasureBundle\Convert;

use Akeneo\Bundle\MeasureBundle\Exception\UnknownFamilyMeasureException;
use Akeneo\Bundle\MeasureBundle\Exception\UnknownMeasureException;
use Akeneo\Bundle\MeasureBundle\Family\WeightFamilyInterface;
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

    function it_allows_to_define_the_family()
    {
        $this->setFamily('Length')->shouldReturnAnInstanceOf('Akeneo\Bundle\MeasureBundle\Convert\MeasureConverter');
    }

    function it_throws_an_exception_if_an_unknown_family_is_set()
    {
        $this
            ->shouldThrow(
                new UnknownFamilyMeasureException()
            )
            ->during('setFamily', array('foo'));
    }

    function it_converts_a_value_from_a_base_unit_to_a_final_unit()
    {
        $this->setFamily('Weight');
        $this->convert(
            WeightFamilyInterface::KILOGRAM,
            WeightFamilyInterface::MILLIGRAM,
            1
        )->shouldReturn((double) 1000000);
    }

    function it_converts_a_value_to_a_standard_unit()
    {
        $this->setFamily('Weight');
        $this->convertBaseToStandard(
            WeightFamilyInterface::MILLIGRAM,
            1000
        )->shouldReturn((double) 1);
    }

    function it_converts_a_standard_value_to_a_final_unit()
    {
        $this->setFamily('Weight');
        $this->convertStandardToResult(
            WeightFamilyInterface::KILOGRAM,
            10
        )->shouldReturn((double) 0.01);
    }

    function it_throws_an_exception_if_the_unit_measure_does_not_exist()
    {
        $this->setFamily('Weight');
        $this
            ->shouldThrow(
                new UnknownMeasureException(
                    'Could not find metric unit "foo" in family "Weight"'
                )
            )
            ->during('convertBaseToStandard', array('foo', Argument::any()));

        $this
            ->shouldThrow(
                new UnknownMeasureException(
                    'Could not find metric unit "foo" in family "Weight"'
                )
            )
            ->during('convertStandardToResult', array('foo', Argument::any()));
    }
}
