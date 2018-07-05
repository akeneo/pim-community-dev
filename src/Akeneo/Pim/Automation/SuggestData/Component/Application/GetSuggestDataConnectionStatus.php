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

namespace Akeneo\Pim\Automation\SuggestData\Component\Application;

use Akeneo\Pim\Automation\SuggestData\Component\Application\ValidateConnectionInterface;
use Akeneo\Pim\Automation\SuggestData\Component\Repository\ConfigurationRepositoryInterface;

/**
 * Checks if a suggest data connection is active or not.
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class GetSuggestDataConnectionStatus
{
    /** @var ConfigurationRepositoryInterface */
    private $configurationRepository;

    /** @var ValidateConnectionInterface */
    private $connectionValidator;

    /**
     * @param ConfigurationRepositoryInterface $configurationRepository
     * @param ValidateConnectionInterface      $connectionValidator
     */
    public function __construct(
        ConfigurationRepositoryInterface $configurationRepository,
        ValidateConnectionInterface $connectionValidator
    ) {
        $this->configurationRepository = $configurationRepository;
        $this->connectionValidator = $connectionValidator;
    }

    /**
     * @param string $code
     *
     * @return bool
     */
    public function forCode(string $code): bool
    {
        $configuration = $this->configurationRepository->findOneByCode($code);
        if (null === $configuration) {
            return false;
        }

        return $this->connectionValidator->validate($configuration->getValues());
    }
}
