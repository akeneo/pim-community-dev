<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\PimEnterprise\Component\SuggestData\Command;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\SuggestData\Command\SaveConfiguration;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class SaveConfigurationSpec extends ObjectBehavior
{
    function it_is_a_save_configuration_command()
    {
        $this->beConstructedWith('foobar', ['foo' => 'bar']);

        $this->shouldHaveType(SaveConfiguration::class);
    }

    function it_returns_a_configuration_code()
    {
        $this->beConstructedWith('foobar', ['foo' => 'bar']);

        $this->getCode()->shouldReturn('foobar');
    }

    function it_returns_a_configuration_fields()
    {
        $this->beConstructedWith('foobar', ['foo' => 'bar']);

        $this->getConfigurationFields()->shouldReturn(['foo' => 'bar']);
    }

    function it_throws_an_exception_during_instantiation_if_code_is_emtpy()
    {
        $this->beConstructedWith('', ['field' => 'value']);

        $this
            ->shouldThrow(new \InvalidArgumentException('Configuration code cannot be empty.'))
            ->duringInstantiation();
    }

    function it_throws_an_exception_during_instantiation_if_configuration_fields_are_emtpy()
    {
        $this->beConstructedWith('foobar', []);

        $this
            ->shouldThrow(new \InvalidArgumentException('Configuration fields cannot be empty.'))
            ->duringInstantiation();
    }

    function it_throws_an_exception_during_instantiation_if_configuration_field_key_is_not_a_string()
    {
        $this->beConstructedWith('foobar', [42 => 'value']);

        $this
            ->shouldThrow(new \InvalidArgumentException(
                'The key of a configuration field must be a string, "integer" given.'
            ))
            ->duringInstantiation();
    }

    function it_throws_an_exception_during_instantiation_if_configuration_field_value_is_not_a_string()
    {
        $this->beConstructedWith('foobar', ['field' => 42]);

        $this
            ->shouldThrow(new \InvalidArgumentException(
                'The value of a configuration field must be a string, "integer" given.'
            ))
            ->duringInstantiation();
    }

    function it_throws_an_exception_during_instantiation_if_configuration_field_value_is_empty()
    {
        $this->beConstructedWith('foobar', ['field' => '']);

        $this
            ->shouldThrow(new \InvalidArgumentException('The value of a configuration field cannot be empty.'))
            ->duringInstantiation();
    }
}
