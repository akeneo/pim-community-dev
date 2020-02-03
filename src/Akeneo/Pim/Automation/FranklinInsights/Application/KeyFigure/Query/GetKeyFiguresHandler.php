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

use Akeneo\Pim\Automation\FranklinInsights\Application\KeyFigure\DataProvider\CreditsProviderInterface;
use Akeneo\Pim\Automation\FranklinInsights\Application\KeyFigure\DataProvider\QualityHighlightsProviderInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\KeyFigure\Model\Read\KeyFigure;
use Akeneo\Pim\Automation\FranklinInsights\Domain\KeyFigure\Model\Read\KeyFigureCollection;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Repository\FranklinAttributeAddedToFamilyRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Repository\FranklinAttributeCreatedRepositoryInterface;

final class GetKeyFiguresHandler
{
    private $attributeCreatedRepository;
    private $attributeAddedToFamilyRepository;
    private $creditsProvider;

    /** @var QualityHighlightsProviderInterface */
    private $qualityHighlightsProvider;

    public function __construct(
        FranklinAttributeCreatedRepositoryInterface $attributeCreatedRepository,
        FranklinAttributeAddedToFamilyRepositoryInterface $attributeAddedToFamilyRepository,
        CreditsProviderInterface $creditsProvider,
        QualityHighlightsProviderInterface $qualityHighlightsProvider
    ) {
        $this->attributeCreatedRepository = $attributeCreatedRepository;
        $this->attributeAddedToFamilyRepository = $attributeAddedToFamilyRepository;
        $this->creditsProvider = $creditsProvider;
        $this->qualityHighlightsProvider = $qualityHighlightsProvider;
    }

    public function handle(GetKeyFiguresQuery $query): KeyFigureCollection
    {
        $creditsKeyFigures = $this->creditsProvider->getCreditsUsageStatistics();
        $qualityHighlightFigures = $this->qualityHighlightsProvider->getKeyFigures();
        $structureKeyFigures = $this->getStructureKeyFigures();

        return $structureKeyFigures->merge($creditsKeyFigures)->merge($qualityHighlightFigures);
    }

    private function getStructureKeyFigures(): KeyFigureCollection
    {
        $attributeCreatedCount = $this->attributeCreatedRepository->count();
        $attributeAddedToFamilyCount = $this->attributeAddedToFamilyRepository->count();

        $attributeCreatedKeyFigure = new KeyFigure('franklin_attribute_created', $attributeCreatedCount);
        $attributeAddedToFamilyKeyFigure = new KeyFigure('franklin_attribute_added_to_family', $attributeAddedToFamilyCount);

        return new KeyFigureCollection(
            [$attributeCreatedKeyFigure, $attributeAddedToFamilyKeyFigure]
        );
    }
}
