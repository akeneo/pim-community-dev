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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\InternalApi\Normalizer;

use Akeneo\Pim\Automation\FranklinInsights\Domain\KeyFigure\Model\Read\KeyFigure;
use Akeneo\Pim\Automation\FranklinInsights\Domain\KeyFigure\Model\Read\KeyFigureCollection;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\InternalApi\Normalizer\KeyFigureCollectionNormalizer;
use PhpSpec\ObjectBehavior;

class KeyFigureCollectionNormalizerSpec extends ObjectBehavior
{
    public function it_is_a_key_figure_collection_normalizer(): void
    {
        $this->shouldHaveType(KeyFigureCollectionNormalizer::class);
    }

    public function it_normalizes_a_key_figure_collection(): void
    {
        $collection = new KeyFigureCollection(
            [
                new KeyFigure(
                    'franklin_attribute_created',
                    3
                ),
                new KeyFigure(
                    'franklin_attribute_added_to_family',
                    2
                ),
            ]
        );

        $this->normalize($collection)->shouldReturn(
            [
                'franklin_attribute_created' => [
                    'type' => KeyFigure::TYPE_NUMBER,
                    'value' => 3,
                ],
                'franklin_attribute_added_to_family' => [
                    'type' => KeyFigure::TYPE_NUMBER,
                    'value' => 2,
                ],
            ]
        );
    }
}
