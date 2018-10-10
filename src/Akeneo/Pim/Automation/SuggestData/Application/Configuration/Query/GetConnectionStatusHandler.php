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

namespace Akeneo\Pim\Automation\SuggestData\Application\Configuration\Query;

use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\DataProviderFactory;
use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\DataProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Read\ConnectionStatus;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ConfigurationRepositoryInterface;

/**
 * Checks if a suggest data connection is active or not.
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class GetConnectionStatusHandler
{
    /** @var ConfigurationRepositoryInterface */
    private $configurationRepository;

    /** @var DataProviderInterface */
    private $dataProvider;

    /**
     * @param ConfigurationRepositoryInterface $configurationRepository
     * @param DataProviderFactory $dataProviderFactory
     */
    public function __construct(
        ConfigurationRepositoryInterface $configurationRepository,
        DataProviderFactory $dataProviderFactory
    ) {
        $this->configurationRepository = $configurationRepository;
        $this->dataProvider = $dataProviderFactory->create();
    }

    /**
     * @return ConnectionStatus
     */
    public function handle(GetConnectionStatusQuery $query): ConnectionStatus
    {
        $configuration = $this->configurationRepository->find();
        if (null === $configuration) {
            return new ConnectionStatus(false);
        }

        $isActive = $this->dataProvider->authenticate($configuration->getToken());

        return new ConnectionStatus($isActive);
    }
}
