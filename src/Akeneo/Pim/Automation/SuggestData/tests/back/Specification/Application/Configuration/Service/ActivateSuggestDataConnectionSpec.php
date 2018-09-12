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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Application\Configuration\Service;

use Akeneo\Pim\Automation\SuggestData\Application\Configuration\Command\SaveConfigurationCommand;
use Akeneo\Pim\Automation\SuggestData\Application\Configuration\Command\SaveConfigurationHandler;
use Akeneo\Pim\Automation\SuggestData\Application\Configuration\Service\ActivateSuggestDataConnection;
use PhpSpec\ObjectBehavior;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class ActivateSuggestDataConnectionSpec extends ObjectBehavior
{
    public function let(SaveConfigurationHandler $saveConnectorConfigurationHandler)
    {
        $this->beConstructedWith($saveConnectorConfigurationHandler);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ActivateSuggestDataConnection::class);
    }

    public function it_activates_a_valid_connection($saveConnectorConfigurationHandler)
    {
        $saveConnectorConfigurationHandler
            ->handle(new SaveConfigurationCommand(['foo' => 'bar']))
            ->shouldBeCalled();

        $this->activate(['foo' => 'bar']);
    }
}
