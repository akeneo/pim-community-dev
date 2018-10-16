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

use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\DataProviderFactory;
use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\DataProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Configuration;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Read\ConnectionStatus;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ConfigurationRepositoryInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class GetSuggestDataConnectionStatusSpec extends ObjectBehavior
{
    public function let(
        ConfigurationRepositoryInterface $configurationRepository,
        DataProviderFactory $dataProviderFactory
    ): void {
        $this->beConstructedWith($configurationRepository, $dataProviderFactory);
    }

    public function it_checks_that_a_connection_is_active(
        DataProviderInterface $dataProvider,
        $dataProviderFactory,
        $configurationRepository
    ): void {
        $configuration = new Configuration(['token' => 'bar']);

        $configurationRepository->find()->willReturn($configuration);
        $dataProviderFactory->create()->willReturn($dataProvider);
        $dataProvider->authenticate('bar')->willReturn(true);

        $this->getStatus()->shouldReturnAnActiveStatus();
    }

    public function it_checks_that_a_connection_is_inactive(
        DataProviderInterface $dataProvider,
        $dataProviderFactory,
        $configurationRepository
    ): void {
        $configuration = new Configuration(['token' => 'bar']);

        $configurationRepository->find()->willReturn($configuration);
        $dataProviderFactory->create()->willReturn($dataProvider);
        $dataProvider->authenticate('bar')->willReturn(false);

        $this->getStatus()->shouldReturnAnInactiveStatus();
    }

    public function it_checks_that_a_connection_does_not_exist($configurationRepository): void
    {
        $configurationRepository->find()->willReturn(null);

        $this->getStatus()->shouldReturnAnInactiveStatus();
    }

    /**
     * {@inheritdoc}
     */
    public function getMatchers(): array
    {
        return [
            'returnAnActiveStatus' => function (ConnectionStatus $connectionStatus) {
                return true === $connectionStatus->isActive();
            },
            'returnAnInactiveStatus' => function (ConnectionStatus $connectionStatus) {
                return false === $connectionStatus->isActive();
            },
        ];
    }
}
