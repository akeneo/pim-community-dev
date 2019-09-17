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

use Akeneo\Pim\Automation\FranklinInsights\Application\KeyFigure\DataProvider\CreditsProviderInterface;
use Akeneo\Pim\Automation\FranklinInsights\Application\KeyFigure\DataProvider\QualityHighlightsProviderInterface;
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
        FranklinAttributeAddedToFamilyRepositoryInterface $attributeAddedToFamilyRepository,
        CreditsProviderInterface $creditsProvider,
        QualityHighlightsProviderInterface $qualityHighlightsProvider
    ): void {
        $this->beConstructedWith($attributeCreatedRepository, $attributeAddedToFamilyRepository, $creditsProvider, $qualityHighlightsProvider);
    }

    public function it_is_a_get_key_figures_handler(): void
    {
        $this->shouldHaveType(GetKeyFiguresHandler::class);
    }

    public function it_handles_a_get_key_figures_query(
        FranklinAttributeCreatedRepositoryInterface $attributeCreatedRepository,
        FranklinAttributeAddedToFamilyRepositoryInterface $attributeAddedToFamilyRepository,
        CreditsProviderInterface $creditsProvider,
        QualityHighlightsProviderInterface $qualityHighlightsProvider
    ): void {
        $attributeCreatedRepository->count()->willReturn(3);
        $attributeAddedToFamilyRepository->count()->willReturn(2);
        $creditsProvider->getCreditsUsageStatistics()->willReturn(new KeyFigureCollection([
            new KeyFigure('credits_consumed', 42),
            new KeyFigure('credits_left', 58),
            new KeyFigure('credits_total', 100),
        ]));
        $qualityHighlightsProvider->getKeyFigures()->willReturn(new KeyFigureCollection([
            new KeyFigure('value_validated', 5),
            new KeyFigure('value_in_error', 3),
            new KeyFigure('value_suggested', 4),
            new KeyFigure('name_and_value_suggested', 10),
        ]));

        $query = new GetKeyFiguresQuery();
        $result = $this->handle($query);

        $result->shouldReturnAnInstanceOf(KeyFigureCollection::class);
        $result->shouldBeLike(
            new KeyFigureCollection(
                [
                    new KeyFigure('franklin_attribute_created', 3),
                    new KeyFigure('franklin_attribute_added_to_family', 2),
                    new KeyFigure('credits_consumed', 42),
                    new KeyFigure('credits_left', 58),
                    new KeyFigure('credits_total', 100),
                    new KeyFigure('value_validated', 5),
                    new KeyFigure('value_in_error', 3),
                    new KeyFigure('value_suggested', 4),
                    new KeyFigure('name_and_value_suggested', 10),
                ]
            )
        );
    }
}
