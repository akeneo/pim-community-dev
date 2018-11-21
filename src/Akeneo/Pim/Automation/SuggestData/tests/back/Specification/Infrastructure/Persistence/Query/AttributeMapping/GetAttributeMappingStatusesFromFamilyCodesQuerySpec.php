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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Infrastructure\Persistence\Query\AttributeMapping;

use Akeneo\Pim\Automation\SuggestData\Domain\AttributeMapping\Query\GetAttributeMappingStatusesFromFamilyCodesQueryInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Persistence\Query\AttributeMapping\GetAttributeMappingStatusesFromFamilyCodesQuery;
use Doctrine\DBAL\Connection;
use PhpSpec\ObjectBehavior;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class GetAttributeMappingStatusesFromFamilyCodesQuerySpec extends ObjectBehavior
{
    public function let(Connection $connection): void
    {
        $this->beConstructedWith($connection);
    }

    public function it_is_a_get_attribute_mapping_statuses_for_families_query(): void
    {
        $this->shouldImplement(GetAttributeMappingStatusesFromFamilyCodesQueryInterface::class);
    }

    public function it_is_a_doctrine_implementation_of_get_attribute_mapping_statuses_for_families_query(): void
    {
        $this->shouldBeAnInstanceOf(GetAttributeMappingStatusesFromFamilyCodesQuery::class);
    }
}
