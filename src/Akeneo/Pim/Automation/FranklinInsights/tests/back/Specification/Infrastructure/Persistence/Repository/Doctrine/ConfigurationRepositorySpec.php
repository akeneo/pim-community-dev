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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Repository\Doctrine;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Repository\ConfigurationRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Repository\Doctrine\ConfigurationRepository;
use Doctrine\DBAL\Connection;
use PhpSpec\ObjectBehavior;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class ConfigurationRepositorySpec extends ObjectBehavior
{
    public function let(Connection $connection): void
    {
        $this->beConstructedWith($connection);
    }

    public function it_is_configuration_repository(): void
    {
        $this->shouldHaveType(ConfigurationRepository::class);
        $this->shouldImplement(ConfigurationRepositoryInterface::class);
    }
}
