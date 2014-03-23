<?php

namespace spec\Akeneo\Bundle\MeasureBundle\Convert;

use PhpSpec\ObjectBehavior;
use Symfony\Component\Yaml\Yaml;

class MeasureConverterSpec extends ObjectBehavior
{
    function let()
    {
        $filename = realpath(dirname(__FILE__) .'/../Resources/config/measure-test.yml');
        if (!file_exists($filename)) {
            throw new \Exception(sprintf('Config file "%s" does not exist', $filename));
        }

        $config = Yaml::parse($filename);

        $this->beConstructedWith($config);
    }

    function it_should_allow_to_define_the_family()
    {
        $this->setFamily('Length')->shouldReturnInstanceOf('Akeneo\Bundle\MeasureBundle\Convert\MeasureConverter');
    }

    function it_should_throw_an_exception_if_an_unknown_family_is_set()
    {

    }
}
