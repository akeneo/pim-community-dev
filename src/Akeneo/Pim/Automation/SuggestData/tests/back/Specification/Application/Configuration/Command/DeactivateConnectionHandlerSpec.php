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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Application\Configuration\Command;

use Akeneo\Pim\Automation\SuggestData\Application\Configuration\Command\DeactivateConnectionCommand;
use Akeneo\Pim\Automation\SuggestData\Application\Configuration\Command\DeactivateConnectionHandler;
use Akeneo\Pim\Automation\SuggestData\Domain\Configuration\Repository\ConfigurationRepositoryInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class DeactivateConnectionHandlerSpec extends ObjectBehavior
{
    public function let(ConfigurationRepositoryInterface $configurationRepository): void
    {
        $this->beConstructedWith($configurationRepository);
    }

    public function it_is_a_deactivate_connection_command_handler(): void
    {
        $this->shouldHaveType(DeactivateConnectionHandler::class);
    }

    public function it_deactivates_a_suggest_data_connection($configurationRepository): void
    {
        $configurationRepository->clear()->shouldBeCalled();

        $this->handle(new DeactivateConnectionCommand());
    }
}
