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

namespace PimEnterprise\Component\SuggestData\Command;

use PimEnterprise\Component\SuggestData\Application\ConnectionIsValidInterface;
use PimEnterprise\Component\SuggestData\Exception\InvalidConnectionConfiguration;
use PimEnterprise\Component\SuggestData\Model\Configuration;
use PimEnterprise\Component\SuggestData\Repository\ConfigurationRepositoryInterface;

/**
 * Handles a "SaveConfiguration" command.
 *
 * it checks that the configuration contained in the command allows to connect
 * to the data provider, then saves it (it can be a new connection creation, or
 * an update of an existing one).
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class SaveConfigurationHandler
{
    /** @var ConnectionIsValidInterface */
    private $pimDotAiConnection;

    /** @var ConfigurationRepositoryInterface */
    private $repository;

    /**
     * @param ConnectionIsValidInterface       $pimDotAiConnection
     * @param ConfigurationRepositoryInterface $repository
     */
    public function __construct(
        ConnectionIsValidInterface $pimDotAiConnection,
        ConfigurationRepositoryInterface $repository
    ) {
        $this->pimDotAiConnection = $pimDotAiConnection;
        $this->repository = $repository;
    }

    /**
     * @param SaveConfiguration $saveConfiguration
     *
     * @throws InvalidConnectionConfiguration
     */
    public function handle(SaveConfiguration $saveConfiguration): void
    {
        if (!$this->pimDotAiConnection->isValid($saveConfiguration->getConfigurationFields())) {
            throw InvalidConnectionConfiguration::forCode($saveConfiguration->getCode());
        }

        $configuration = $this->repository->find((string) $saveConfiguration->getCode());

        if (null === $configuration) {
            $configuration = new Configuration(
                $saveConfiguration->getCode(),
                $saveConfiguration->getConfigurationFields()
            );
        } else {
            $configuration->setConfigurationFields($saveConfiguration->getConfigurationFields());
        }

        $this->repository->save($configuration);
    }
}
