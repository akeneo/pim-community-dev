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

namespace spec\PimEnterprise\Component\SuggestData\Application;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\SuggestData\Command\SaveConfiguration;
use PimEnterprise\Component\SuggestData\Command\SaveConfigurationHandler;
use PimEnterprise\Component\SuggestData\Application\SuggestDataConnection;
use PimEnterprise\Component\SuggestData\Exception\InvalidConnectionConfiguration;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class SuggestDataConnectionSpec extends ObjectBehavior
{
    function let(SaveConfigurationHandler $saveConnectorConfigurationHandler)
    {
        $this->beConstructedWith($saveConnectorConfigurationHandler);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SuggestDataConnection::class);
    }

    function it_activates_a_valid_connection($saveConnectorConfigurationHandler)
    {
        $saveConnectorConfigurationHandler
            ->handle(new SaveConfiguration('foobar', ['foo' => 'bar']))
            ->shouldBeCalled();

        $this->activate('foobar', ['foo' => 'bar'])->shouldReturn(true);
    }

    function it_does_not_activate_an_invalid_connection($saveConnectorConfigurationHandler)
    {
        $saveConnectorConfigurationHandler
            ->handle(new SaveConfiguration('foobar', ['bar' => 'baz']))
            ->willThrow(new InvalidConnectionConfiguration('foobar'));

        $this->activate('foobar', ['bar' => 'baz'])->shouldReturn(false);
    }
}
