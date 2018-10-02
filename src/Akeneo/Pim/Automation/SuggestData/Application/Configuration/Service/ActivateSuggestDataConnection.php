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

namespace Akeneo\Pim\Automation\SuggestData\Application\Configuration\Service;

use Akeneo\Pim\Automation\SuggestData\Application\Configuration\Command\SaveConfigurationCommand;
use Akeneo\Pim\Automation\SuggestData\Application\Configuration\Command\SaveConfigurationHandler;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class ActivateSuggestDataConnection
{
    /** @var SaveConfigurationHandler */
    private $saveConfigurationHandler;

    /**
     * @param SaveConfigurationHandler $saveConfigurationHandler
     */
    public function __construct(SaveConfigurationHandler $saveConfigurationHandler)
    {
        $this->saveConfigurationHandler = $saveConfigurationHandler;
    }

    /**
     * Activates the connection to the the data provider.
     * Throws an invalid argument exception if anything goes wrong during the activation.
     *
     * @param array $configuration
     *
     * @throws \Akeneo\Pim\Automation\SuggestData\Domain\Exception\InvalidConnectionConfigurationException
     */
    public function activate(array $configuration): void
    {
        $saveConfiguration = new SaveConfigurationCommand($configuration);
        $this->saveConfigurationHandler->handle($saveConfiguration);
    }
}
