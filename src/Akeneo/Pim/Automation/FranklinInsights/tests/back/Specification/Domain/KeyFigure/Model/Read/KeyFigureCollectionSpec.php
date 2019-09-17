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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Domain\KeyFigure\Model\Read;

use Akeneo\Pim\Automation\FranklinInsights\Domain\KeyFigure\Model\Read\KeyFigure;
use Akeneo\Pim\Automation\FranklinInsights\Domain\KeyFigure\Model\Read\KeyFigureCollection;
use PhpSpec\ObjectBehavior;

class KeyFigureCollectionSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(
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
        );
    }

    public function it_is_a_key_figure_collection(): void
    {
        $this->shouldHaveType(KeyFigureCollection::class);
    }

    public function it_is_traversable(): void
    {
        $this->shouldHaveType(\Traversable::class);

        $this->getIterator()->shouldReturnAnInstanceOf(\Iterator::class);
    }

    public function it_iterates_on_key_figures(): void
    {
        $this->shouldIterateLike(
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
        );
    }

    public function it_merges_key_figures(): void
    {
        $keyFigures = new KeyFigureCollection([
            new KeyFigure('credits_consumed', 42),
        ]);

        $this->merge($keyFigures)->shouldBeLike(new KeyFigureCollection([
            new KeyFigure('franklin_attribute_created', 3),
            new KeyFigure('franklin_attributed_added_to_family', 2),
            new KeyFigure('credits_consumed', 42),
        ]));
    }
}
