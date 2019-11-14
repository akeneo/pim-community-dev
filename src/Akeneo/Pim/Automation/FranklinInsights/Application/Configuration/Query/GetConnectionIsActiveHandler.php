<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Repository\ConfigurationRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\ValueObject\Token;

class GetConnectionIsActiveHandler
{
    /** @var ConfigurationRepositoryInterface */
    private $configurationRepository;

    public function __construct(ConfigurationRepositoryInterface $configurationRepository)
    {
        $this->configurationRepository = $configurationRepository;
    }

    public function handle(GetConnectionIsActiveQuery $query): bool
    {
        $configuration = $this->configurationRepository->find();

        return $configuration->getToken() instanceof Token;
    }
}
