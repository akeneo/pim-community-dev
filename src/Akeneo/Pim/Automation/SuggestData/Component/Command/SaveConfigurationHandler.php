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

namespace Akeneo\Pim\Automation\SuggestData\Component\Command;

use Akeneo\Pim\Automation\SuggestData\Component\Application\ValidateConnectionInterface;
use Akeneo\Pim\Automation\SuggestData\Component\Exception\InvalidConnectionConfiguration;
use Akeneo\Pim\Automation\SuggestData\Component\Model\Configuration;
use Akeneo\Pim\Automation\SuggestData\Component\Repository\ConfigurationRepositoryInterface;

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
    /** @var ValidateConnectionInterface */
    private $connectionValidator;

    /** @var ConfigurationRepositoryInterface */
    private $repository;

    /**
     * @param ValidateConnectionInterface      $connectionValidator
     * @param ConfigurationRepositoryInterface $repository
     */
    public function __construct(
        ValidateConnectionInterface $connectionValidator,
        ConfigurationRepositoryInterface $repository
    ) {
        $this->connectionValidator = $connectionValidator;
        $this->repository = $repository;
    }

    /**
     * @param SaveConfiguration $saveConfiguration
     *
     * @throws InvalidConnectionConfiguration
     */
    public function handle(SaveConfiguration $saveConfiguration): void
    {
        if (!$this->connectionValidator->validate($saveConfiguration)) {
            throw InvalidConnectionConfiguration::forCode($saveConfiguration->getCode());
        }

        $configuration = $this->repository->findOneByCode($saveConfiguration->getCode());

        if (null === $configuration) {
            $configuration = new Configuration(
                $saveConfiguration->getCode(),
                $saveConfiguration->getValues()
            );
        } else {
            $configuration->setValues($saveConfiguration->getValues());
        }

        $this->repository->save($configuration);
    }
}
