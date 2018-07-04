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

namespace spec\Akeneo\Pim\Automation\SuggestData\Component\Exception;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Automation\SuggestData\Component\Exception\InvalidConnectionConfiguration;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class InvalidConnectionConfigurationSpec extends ObjectBehavior
{
    function it_is_an_invalid_connection_configuration_exception()
    {
        $this->shouldHaveType(InvalidConnectionConfiguration::class);
    }

    function it_is_a_logic_exception()
    {
        $this->shouldHaveType(\LogicException::class);
    }

    function it_instanciates_itself()
    {
        $this->beConstructedThrough('forCode', ['foobar']);

        $this->getMessage()->shouldReturn('Provided configuration for connection to "foobar" is invalid.');
    }
}
