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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query;

use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusQuery;
use Akeneo\Pim\Automation\FranklinInsights\Application\DataProvider\AuthenticationProviderInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Model\Configuration;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Model\Read\ConnectionStatus;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Repository\ConfigurationRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\ValueObject\Token;
use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Repository\IdentifiersMappingRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Repository\ProductSubscriptionRepositoryInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class GetConnectionStatusHandlerSpec extends ObjectBehavior
{
    public function let(
        ConfigurationRepositoryInterface $configurationRepository,
        AuthenticationProviderInterface $authenticationProvider,
        IdentifiersMappingRepositoryInterface $identifiersMappingRepository,
        ProductSubscriptionRepositoryInterface $productSubscriptionRepository,
        IdentifiersMapping $identifiersMapping
    ): void {
        $this->beConstructedWith(
            $configurationRepository,
            $authenticationProvider,
            $identifiersMappingRepository,
            $productSubscriptionRepository
        );

        $configuration = new Configuration();
        $configuration->setToken(new Token('bar'));

        $configurationRepository->find()->willReturn($configuration);

        $identifiersMappingRepository->find()->willReturn($identifiersMapping);
        $identifiersMapping->isValid()->willReturn(true);

        $productSubscriptionRepository->count()->willReturn(0);
    }

    public function it_checks_that_a_connection_is_active(): void
    {
        $this->handle(new GetConnectionStatusQuery(false))->shouldReturnAnActiveStatus();
    }

    public function it_checks_that_a_connection_is_inactive($configurationRepository): void
    {
        $configurationRepository->find()->willReturn(new Configuration());

        $this->handle(new GetConnectionStatusQuery(false))->shouldReturnAnInactiveStatus();
    }

    public function it_checks_that_an_identifiers_mapping_is_valid(): void
    {
        $this->handle(new GetConnectionStatusQuery(false))->shouldReturnValidIdentifiersMappingStatus();
    }

    public function it_checks_that_an_identifiers_mapping_is_invalid($identifiersMapping): void
    {
        $identifiersMapping->isValid()->willReturn(false);
        $this->handle(new GetConnectionStatusQuery(false))->shouldReturnInvalidIdentifiersMappingStatus();
    }

    public function it_checks_that_it_counts_product_subscriptions($productSubscriptionRepository): void
    {
        $productSubscriptionRepository->count()->willReturn(42);
        $this->handle(new GetConnectionStatusQuery(false))->shouldReturnProductSubscriptions();
    }

    public function it_checks_that_a_connection_is_valid($authenticationProvider): void
    {
        $authenticationProvider->authenticate('bar')->willReturn(true);

        $expectedConnectionStatus = new ConnectionStatus(true, true, true, 0);
        $this->handle(new GetConnectionStatusQuery(true))->shouldBeLike($expectedConnectionStatus);
    }

    public function it_checks_that_a_connection_is_invalid($authenticationProvider): void
    {
        $authenticationProvider->authenticate('bar')->willReturn(false);

        $expectedConnectionStatus = new ConnectionStatus(true, false, true, 0);
        $this->handle(new GetConnectionStatusQuery(true))->shouldBeLike($expectedConnectionStatus);
    }

    /**
     * {@inheritdoc}
     */
    public function getMatchers(): array
    {
        return [
            'returnAnActiveStatus' => function (ConnectionStatus $connectionStatus) {
                return $connectionStatus->isActive();
            },
            'returnAnInactiveStatus' => function (ConnectionStatus $connectionStatus) {
                return !$connectionStatus->isActive();
            },
            'returnValidIdentifiersMappingStatus' => function (ConnectionStatus $connectionStatus) {
                return $connectionStatus->isIdentifiersMappingValid();
            },
            'returnInvalidIdentifiersMappingStatus' => function (ConnectionStatus $connectionStatus) {
                return !$connectionStatus->isIdentifiersMappingValid();
            },
            'returnProductSubscriptions' => function (ConnectionStatus $connectionStatus) {
                return 42 === $connectionStatus->productSubscriptionCount();
            },
        ];
    }
}
