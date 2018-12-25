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

namespace Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Model\Configuration;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Repository\ConfigurationRepositoryInterface;

/**
 * Retrieves a suggest data configuration and returns it normalized.
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class GetConfigurationHandler
{
    /** @var ConfigurationRepositoryInterface */
    private $repository;

    /**
     * @param ConfigurationRepositoryInterface $repository
     */
    public function __construct(ConfigurationRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param GetConfigurationQuery $query
     *
     * @return Configuration
     */
    public function handle(GetConfigurationQuery $query): Configuration
    {
        return $this->repository->find();
    }
}
