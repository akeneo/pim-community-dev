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

use Akeneo\Pim\Automation\SuggestData\Domain\Configuration\Repository\ConfigurationRepositoryInterface;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class DeactivateConnectionHandler
{
    /** @var ConfigurationRepositoryInterface */
    private $configurationRepository;

    /**
     * @param ConfigurationRepositoryInterface $configurationRepository
     */
    public function __construct(ConfigurationRepositoryInterface $configurationRepository)
    {
        $this->configurationRepository = $configurationRepository;
    }

    /**
     * @param DeactivateConnectionCommand $command
     */
    public function handle(DeactivateConnectionCommand $command): void
    {
        $this->configurationRepository->clear();
    }
}
