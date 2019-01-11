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

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Repository;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Model\Configuration;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
interface ConfigurationRepositoryInterface
{
    /**
     * Returns Franklin configuration.
     *
     * @return Configuration
     */
    public function find(): Configuration;

    /**
     * Saves a configuration.
     *
     * @param Configuration $configuration
     */
    public function save(Configuration $configuration): void;

    /**
     * Clears the current configuration.
     */
    public function clear(): void;
}
