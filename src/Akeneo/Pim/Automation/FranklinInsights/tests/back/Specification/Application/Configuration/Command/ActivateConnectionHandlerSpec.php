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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Command;

use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Command\ActivateConnectionCommand;
use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Command\ActivateConnectionHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Validator\ConnectionValidator;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Exception\ConnectionConfigurationException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Model\Configuration;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Repository\ConfigurationRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\ValueObject\Token;
use PhpSpec\ObjectBehavior;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class ActivateConnectionHandlerSpec extends ObjectBehavior
{
    public function let(
        ConnectionValidator $connectionValidator,
        ConfigurationRepositoryInterface $repository
    ): void {
        $this->beConstructedWith($connectionValidator, $repository);
    }

    public function it_is_a_save_connector_configuration_command_handler(): void
    {
        $this->shouldHaveType(ActivateConnectionHandler::class);
    }

    public function it_updates_an_existing_configuration_if_token_valid($connectionValidator, $repository): void
    {
        $token = new Token('bar');
        $command = new ActivateConnectionCommand($token);

        $configuration = new Configuration();
        $configuration->setToken($token);

        $connectionValidator->isTokenValid($token)->willReturn(true);
        $repository->find()->willReturn($configuration);

        $repository->save($configuration)->shouldBeCalled();

        $this->handle($command);
    }

    public function it_saves_a_new_configuration_if_token_valid($connectionValidator, $repository): void
    {
        $token = new Token('bar');
        $command = new ActivateConnectionCommand($token);

        $configuration = new Configuration();
        $configuration->setToken($token);

        $connectionValidator->isTokenValid($token)->willReturn(true);
        $repository->find()->willReturn(new Configuration());

        $repository->save($configuration)->shouldBeCalled();

        $this->handle($command);
    }

    public function it_throws_an_exception_if_configuration_is_invalid($connectionValidator): void
    {
        $token = new Token('foo');
        $command = new ActivateConnectionCommand($token);

        $connectionValidator->isTokenValid($token)->willReturn(false);

        $this->shouldThrow(ConnectionConfigurationException::class)->during('handle', [$command]);
    }
}
