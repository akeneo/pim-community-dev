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

namespace Akeneo\Pim\Automation\FranklinInsights\Application\KeyFigure\Query;

use Akeneo\Pim\Automation\FranklinInsights\Domain\KeyFigure\Model\Read\KeyFigure;
use Akeneo\Pim\Automation\FranklinInsights\Domain\KeyFigure\Model\Read\KeyFigureCollection;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Query\CountFranklinAttributesAddedToFamiliesQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Query\CountFranklinAttributesCreatedQueryInterface;

final class GetKeyMeasurementsHandler
{
    /** @var CountFranklinAttributesCreatedQueryInterface */
    private $countFranklinAttributesCreatedQuery;

    /** @var CountFranklinAttributesAddedToFamiliesQueryInterface */
    private $countFranklinAttributesAddedToFamiliesQuery;

    public function __construct(
        CountFranklinAttributesCreatedQueryInterface $countFranklinAttributesCreatedQuery,
        CountFranklinAttributesAddedToFamiliesQueryInterface $countFranklinAttributesAddedToFamiliesQuery
    ) {
        $this->countFranklinAttributesCreatedQuery = $countFranklinAttributesCreatedQuery;
        $this->countFranklinAttributesAddedToFamiliesQuery = $countFranklinAttributesAddedToFamiliesQuery;
    }

    public function handle(GetKeyMeasurementsQuery $query): KeyFigureCollection
    {
        return new KeyFigureCollection(
            [
                new KeyFigure(
                    'franklin_attribute_created',
                    $this->countFranklinAttributesCreatedQuery->execute()
                ),
                new KeyFigure(
                    'franklin_attributed_added_to_family',
                    $this->countFranklinAttributesAddedToFamiliesQuery->execute()
                ),
            ]
        );
    }
}
