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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Validator;

use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Validator\ConnectionValidator;
use Akeneo\Pim\Automation\FranklinInsights\Application\DataProvider\AuthenticationProviderInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Model\Configuration;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Repository\ConfigurationRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\ValueObject\Token;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class ConnectionValidatorSpec extends ObjectBehavior
{
    public function let(
        AuthenticationProviderInterface $authenticationProvider,
        ConfigurationRepositoryInterface $configurationRepository
    ): void {
        $this->beConstructedWith($authenticationProvider, $configurationRepository);
    }

    public function it_is_a_connection_validator(): void
    {
        $this->shouldBeAnInstanceOf(ConnectionValidator::class);
    }

    public function it_returns_true_if_a_token_is_valid($authenticationProvider): void
    {
        $token = new Token('valid-token');
        $authenticationProvider->authenticate($token)->willReturn(true);

        $this->isTokenValid($token)->shouldReturn(true);
    }

    public function it_returns_false_if_a_token_is_invalid($authenticationProvider): void
    {
        $token = new Token('invalid-token');
        $authenticationProvider->authenticate($token)->willReturn(false);

        $this->isTokenValid($token)->shouldReturn(false);
    }

    public function it_returns_true_if_the_current_saved_token_is_valid(
        $configurationRepository,
        $authenticationProvider
    ): void {
        $token = new Token('valid-token');
        $configuration = new Configuration();
        $configuration->setToken($token);
        $configurationRepository->find()->willReturn($configuration);

        $authenticationProvider->authenticate($token)->willReturn(true);

        $this->isValid()->shouldReturn(true);
    }

    public function it_returns_false_if_the_current_save_token_is_invalid(
        $configurationRepository,
        $authenticationProvider
    ): void {
        $token = new Token('invalid-token');
        $configuration = new Configuration();
        $configuration->setToken($token);
        $configurationRepository->find()->willReturn($configuration);

        $authenticationProvider->authenticate($token)->willReturn(false);

        $this->isValid()->shouldReturn(false);
    }
}
