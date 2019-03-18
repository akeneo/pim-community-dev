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

use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Repository\FamilyMappingStatusRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Repository\Doctrine\FamilyMappingStatusRepository;
use Doctrine\DBAL\Connection;
use PhpSpec\ObjectBehavior;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class FamilyMappingStatusRepositorySpec extends ObjectBehavior
{
    public function let(Connection $connection): void
    {
        $this->beConstructedWith($connection);
    }

    public function it_is_a_family_mapping_status_repository(): void
    {
        $this->shouldImplement(FamilyMappingStatusRepositoryInterface::class);
    }

    public function it_is_the_doctrine_implementation_of_the_family_repository(): void
    {
        $this->shouldHaveType(FamilyMappingStatusRepository::class);
    }
}
