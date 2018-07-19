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

namespace spec\Akeneo\Pim\Automation\SuggestData\Component\Application;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Automation\SuggestData\Component\Command\SaveConfiguration;
use Akeneo\Pim\Automation\SuggestData\Component\Command\SaveConfigurationHandler;
use Akeneo\Pim\Automation\SuggestData\Component\Application\ActivateSuggestDataConnection;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class ActivateSuggestDataConnectionSpec extends ObjectBehavior
{
    function let(SaveConfigurationHandler $saveConnectorConfigurationHandler)
    {
        $this->beConstructedWith($saveConnectorConfigurationHandler);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ActivateSuggestDataConnection::class);
    }

    function it_activates_a_valid_connection($saveConnectorConfigurationHandler)
    {
        $saveConnectorConfigurationHandler
            ->handle(new SaveConfiguration('foobar', ['foo' => 'bar']))
            ->shouldBeCalled();

        $this->activate('foobar', ['foo' => 'bar']);
    }
}
