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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Application\KeyFigure\Query;

use Akeneo\Pim\Automation\FranklinInsights\Application\KeyFigure\Query\GetKeyFiguresHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\KeyFigure\Query\GetKeyFiguresQuery;
use Akeneo\Pim\Automation\FranklinInsights\Domain\KeyFigure\Model\Read\KeyFigure;
use Akeneo\Pim\Automation\FranklinInsights\Domain\KeyFigure\Model\Read\KeyFigureCollection;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Repository\FranklinAttributeAddedToFamilyRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Repository\FranklinAttributeCreatedRepositoryInterface;
use PhpSpec\ObjectBehavior;

class GetKeyFiguresHandlerSpec extends ObjectBehavior
{
    public function let(
        FranklinAttributeCreatedRepositoryInterface $attributeCreatedRepository,
        FranklinAttributeAddedToFamilyRepositoryInterface $attributeAddedToFamilyRepository
    ): void {
        $this->beConstructedWith($attributeCreatedRepository, $attributeAddedToFamilyRepository);
    }

    public function it_is_a_get_key_measurements_handler(): void
    {
        $this->shouldHaveType(GetKeyFiguresHandler::class);
    }

    public function it_handles_a_get_key_measurements_query(
        $attributeCreatedRepository,
        $attributeAddedToFamilyRepository
    ): void {
        $attributeCreatedRepository->count()->willReturn(3);
        $attributeAddedToFamilyRepository->count()->willReturn(2);

        $query = new GetKeyFiguresQuery();
        $result = $this->handle($query);

        $result->shouldReturnAnInstanceOf(KeyFigureCollection::class);
        $result->shouldIterateLike(
            new KeyFigureCollection(
                [
                    new KeyFigure('franklin_attribute_created', 3),
                    new KeyFigure('franklin_attributed_added_to_family', 2),
                ]
            )
        );
    }
}
