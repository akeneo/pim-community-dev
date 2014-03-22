<?php

namespace spec\Akeneo\Bundle\MeasureBundle\DependencyInjection;

use PhpSpec\ObjectBehavior;

class ConfigurationSpec extends ObjectBehavior
{
    function it_should_be_an_instance_of_tree_builder()
    {
        $this->getConfigTreeBuilder()->shouldBeAnInstanceOf('Symfony\Component\Config\Definition\Builder\TreeBuilder');
    }
}
