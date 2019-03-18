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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query;

use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query\SearchFamiliesHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query\SearchFamiliesQuery;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Read\FamilyMappingStatus;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Read\FamilyMappingStatusCollection;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Repository\FamilyMappingStatusRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Model\Read\Family;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use PhpSpec\ObjectBehavior;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class SearchFamiliesHandlerSpec extends ObjectBehavior
{
    public function let(FamilyMappingStatusRepositoryInterface $familyRepository): void
    {
        $this->beConstructedWith($familyRepository);
    }

    public function it_handles_a_get_families_query(): void
    {
        $this->shouldHaveType(SearchFamiliesHandler::class);
    }

    public function it_returns_a_collection_of_families($familyRepository): void
    {
        $familyCollection = new FamilyMappingStatusCollection();
        $familyCollection->add(new FamilyMappingStatus(
            new Family(new FamilyCode('family_code_1'), ['en_US' => 'Family 1']),
            FamilyMappingStatus::MAPPING_PENDING
        ));
        $familyCollection->add(new FamilyMappingStatus(
            new Family(new FamilyCode('family_code_2'), ['en_US' => 'Family 2']),
            FamilyMappingStatus::MAPPING_FULL
        ));

        $familyRepository->findBySearch(1, 10, null)->willReturn($familyCollection);

        $query = new SearchFamiliesQuery(10, 1, null);
        $this->handle($query)->shouldReturn($familyCollection);
    }
}
