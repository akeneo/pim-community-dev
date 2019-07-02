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
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Repository\FranklinAttributeAddedToFamilyRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Repository\FranklinAttributeCreatedRepositoryInterface;

final class GetKeyFiguresHandler
{
    private $attributeCreatedRepository;
    private $attributeAddedToFamilyRepository;

    public function __construct(
        FranklinAttributeCreatedRepositoryInterface $attributeCreatedRepository,
        FranklinAttributeAddedToFamilyRepositoryInterface $attributeAddedToFamilyRepository
    ) {
        $this->attributeCreatedRepository = $attributeCreatedRepository;
        $this->attributeAddedToFamilyRepository = $attributeAddedToFamilyRepository;
    }

    public function handle(GetKeyFiguresQuery $query): KeyFigureCollection
    {
        $attributeCreatedCount = $this->attributeCreatedRepository->count();
        $attributeAddedToFamilyCount = $this->attributeAddedToFamilyRepository->count();

        $attributeCreatedKeyFigure = new KeyFigure('franklin_attribute_created', $attributeCreatedCount);
        $attributeAddedToFamilyKeyFigure = new KeyFigure('franklin_attributed_added_to_family', $attributeAddedToFamilyCount);

        return new KeyFigureCollection(
            [$attributeCreatedKeyFigure, $attributeAddedToFamilyKeyFigure]
        );
    }
}
