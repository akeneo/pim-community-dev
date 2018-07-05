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

use Akeneo\Pim\Automation\SuggestData\Component\Command\SaveConfiguration;
use Akeneo\Pim\Automation\SuggestData\Component\Command\SaveConfigurationHandler;
use Akeneo\Pim\Automation\SuggestData\Component\Exception\InvalidConnectionConfiguration;

/**
 * Activates the connection to the the data provider.
 *
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
     * @param string $code
     * @param array  $configuration
     *
     * @return bool
     */
    public function activate(string $code, array $configuration): bool
    {
        try {
            $saveConfiguration = new SaveConfiguration($code, $configuration);
            $this->saveConfigurationHandler->handle($saveConfiguration);
        } catch (InvalidConnectionConfiguration $exception) {
            return false;
        }

        return true;
    }
}
