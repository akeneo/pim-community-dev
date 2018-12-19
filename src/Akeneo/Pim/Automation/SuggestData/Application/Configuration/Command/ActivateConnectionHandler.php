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

use Akeneo\Pim\Automation\SuggestData\Application\Configuration\Validator\ConnectionValidator;
use Akeneo\Pim\Automation\SuggestData\Domain\Configuration\Exception\ConnectionConfigurationException;
use Akeneo\Pim\Automation\SuggestData\Domain\Configuration\Repository\ConfigurationRepositoryInterface;

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
    /** @var ConnectionValidator */
    private $connectionValidator;

    /** @var ConfigurationRepositoryInterface */
    private $repository;

    /**
     * @param ConnectionValidator $connectionValidator
     * @param ConfigurationRepositoryInterface $repository
     */
    public function __construct(
        ConnectionValidator $connectionValidator,
        ConfigurationRepositoryInterface $repository
    ) {
        $this->connectionValidator = $connectionValidator;
        $this->repository = $repository;
    }

    /**
     * @param ActivateConnectionCommand $command
     *
     * @throws ConnectionConfigurationException
     */
    public function handle(ActivateConnectionCommand $command): void
    {
        $isAuthenticated = $this->connectionValidator->isTokenValid($command->token());
        if (true !== $isAuthenticated) {
            throw ConnectionConfigurationException::invalidToken();
        }

        $configuration = $this->repository->find();
        $configuration->setToken($command->token());

        $this->repository->save($configuration);
    }
}
