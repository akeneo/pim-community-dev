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

use Akeneo\Pim\Automation\FranklinInsights\Application\KeyFigure\Query\GetKeyMeasurementsHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\KeyFigure\Query\GetKeyMeasurementsQuery;
use Akeneo\Pim\Automation\FranklinInsights\Domain\KeyFigure\Model\Read\KeyFigure;
use Akeneo\Pim\Automation\FranklinInsights\Domain\KeyFigure\Model\Read\KeyFigureCollection;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Query\CountFranklinAttributesAddedToFamiliesQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Query\CountFranklinAttributesCreatedQueryInterface;
use PhpSpec\ObjectBehavior;

class GetKeyMeasurementsHandlerSpec extends ObjectBehavior
{
    public function let(
        CountFranklinAttributesCreatedQueryInterface $countFranklinAttributesCreatedQuery,
        CountFranklinAttributesAddedToFamiliesQueryInterface $countFranklinAttributesAddedToFamiliesQuery
    ): void {
        $this->beConstructedWith($countFranklinAttributesCreatedQuery, $countFranklinAttributesAddedToFamiliesQuery);
    }

    public function it_is_a_get_key_measurements_handler(): void
    {
        $this->shouldHaveType(GetKeyMeasurementsHandler::class);
    }

    public function it_handles_a_get_key_measurements_query(
        $countFranklinAttributesCreatedQuery,
        $countFranklinAttributesAddedToFamiliesQuery
    ): void {
        $countFranklinAttributesCreatedQuery->execute()->willReturn(3);
        $countFranklinAttributesAddedToFamiliesQuery->execute()->willReturn(2);

        $query = new GetKeyMeasurementsQuery();
        $result = $this->handle($query);

        $result->shouldReturnAnInstanceOf(KeyFigureCollection::class);
        $result->shouldIterateLike(
            new KeyFigureCollection(
                [
                    new KeyFigure(
                        'franklin_attribute_created',
                        3
                    ),
                    new KeyFigure(
                        'franklin_attributed_added_to_family',
                        2
                    ),
                ]
            )
        );
    }
}
