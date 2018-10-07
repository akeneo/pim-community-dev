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

namespace Akeneo\Pim\Automation\SuggestData\Application\Configuration\Command;

use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\DataProviderFactory;
use Akeneo\Pim\Automation\SuggestData\Domain\Exception\InvalidConnectionConfigurationException;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Configuration;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ConfigurationRepositoryInterface;

/**
 * Handles a "SaveConfiguration" command.
 *
 * it checks that the configuration contained in the command allows to connect
 * to the data provider, then saves it (it can be a new connection creation, or
 * an update of an existing one).
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class ActivateConnectionHandler
{
    /** @var DataProviderFactory */
    private $dataProviderFactory;

    /** @var ConfigurationRepositoryInterface */
    private $repository;

    /**
     * @param DataProviderFactory $dataProviderFactory
     * @param ConfigurationRepositoryInterface $repository
     */
    public function __construct(
        DataProviderFactory $dataProviderFactory,
        ConfigurationRepositoryInterface $repository
    ) {
        $this->dataProviderFactory = $dataProviderFactory;
        $this->repository = $repository;
    }

    /**
     * @param ActivateConnectionCommand $saveConfiguration
     *
     * @throws InvalidConnectionConfigurationException
     */
    public function handle(ActivateConnectionCommand $saveConfiguration): void
    {
        $dataProvider = $this->dataProviderFactory->create();
        $isAuthenticated = $dataProvider->authenticate($saveConfiguration->getValues()['token']);
        if (true !== $isAuthenticated) {
            throw new InvalidConnectionConfigurationException(
                sprintf('Provided configuration is invalid.')
            );
        }

        $configuration = $this->repository->find();

        if (null === $configuration) {
            $configuration = new Configuration(
                $saveConfiguration->getValues()
            );
        } else {
            $configuration->setValues($saveConfiguration->getValues());
        }

        $this->repository->save($configuration);
    }
}
