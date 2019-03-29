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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Repository\Doctrine;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Repository\FamilyRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Repository\Doctrine\FamilyRepository;
use Doctrine\DBAL\Connection;
use PhpSpec\ObjectBehavior;

class FamilyRepositorySpec extends ObjectBehavior
{
    public function let(Connection $connection): void
    {
        $this->beConstructedWith($connection);
    }

    public function it_is_an_attribute_mapping_family_repository(): void
    {
        $this->shouldImplement(FamilyRepositoryInterface::class);
    }

    public function it_is_the_doctrine_implementation_of_the_family_repository(): void
    {
        $this->shouldHaveType(FamilyRepository::class);
    }
}
