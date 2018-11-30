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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Application\Mapping\Query;

use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Query\SearchFamiliesHandler;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Query\SearchFamiliesQuery;
use Akeneo\Pim\Automation\SuggestData\Domain\AttributeMapping\Model\Read\Family;
use Akeneo\Pim\Automation\SuggestData\Domain\AttributeMapping\Model\Read\FamilyCollection;
use Akeneo\Pim\Automation\SuggestData\Domain\AttributeMapping\Repository\FamilyRepositoryInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class SearchFamiliesHandlerSpec extends ObjectBehavior
{
    public function let(FamilyRepositoryInterface $familyRepository): void
    {
        $this->beConstructedWith($familyRepository);
    }

    public function it_handles_a_get_families_query(): void
    {
        $this->shouldHaveType(SearchFamiliesHandler::class);
    }

    public function it_returns_a_collection_of_families($familyRepository): void
    {
        $familyCollection = new FamilyCollection();
        $familyCollection->add(new Family('family_code_1', ['en_US' => 'Family 1'], Family::MAPPING_PENDING));
        $familyCollection->add(new Family('family_code_2', ['en_US' => 'Family 2'], Family::MAPPING_FULL));

        $familyRepository->findBySearch(1, 10, null)->willReturn($familyCollection);

        $query = new SearchFamiliesQuery(10, 1, null);
        $this->handle($query)->shouldReturn($familyCollection);
    }
}
