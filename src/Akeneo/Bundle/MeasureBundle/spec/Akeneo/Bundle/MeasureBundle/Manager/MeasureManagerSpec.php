<?php

namespace spec\Akeneo\Bundle\MeasureBundle\Manager;

use Akeneo\Bundle\MeasureBundle\Family\WeightFamilyInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Yaml\Yaml;

/**
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MeasureManagerSpec extends ObjectBehavior
{
    function let()
    {
        $filename = realpath(dirname(__FILE__) .'/../Resources/config/measure-test.yml');
        if (!file_exists($filename)) {
            throw new \Exception(sprintf('Config file "%s" does not exist', $filename));
        }

        $config = Yaml::parse(file_get_contents($filename));

        $this->setMeasureConfig($config['measures_config']);
    }

    function it_throws_an_exception_when_try_to_get_symbols_of_unknown_family()
    {
        $this
            ->shouldThrow(
                new \InvalidArgumentException('Undefined measure family "foo"')
            )
            ->during('getUnitSymbolsForFamily', array('foo'));
    }

    function it_returns_unit_symbols_list_from_a_family()
    {
        $this
            ->getUnitSymbolsForFamily(WeightFamilyInterface::FAMILY)
            ->shouldReturn(
                array(
                    'MILLIGRAM' => 'mg',
                    'GRAM' => 'g',
                    'KILOGRAM' => 'kg'
                )
            );
    }

    function it_returns_standard_unit_for_a_family()
    {
        $this
            ->getStandardUnitForFamily(WeightFamilyInterface::FAMILY)
            ->shouldReturn(WeightFamilyInterface::GRAM);
    }
}
