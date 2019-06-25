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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\InternalApi\Normalizer;

use Akeneo\Pim\Automation\FranklinInsights\Domain\KeyFigure\Model\Read\KeyFigure;
use Akeneo\Pim\Automation\FranklinInsights\Domain\KeyFigure\Model\Read\KeyFigureCollection;

final class KeyFigureCollectionNormalizer
{
    public function normalize(KeyFigureCollection $keyFigureCollection): array
    {
        $data = [];

        /** @var KeyFigure $keyFigure */
        foreach ($keyFigureCollection as $keyFigure) {
            $data[$keyFigure->getName()] = [
                'type' => $keyFigure->getType(),
                'value' => $keyFigure->getValue(),
            ];
        }

        return $data;
    }
}
