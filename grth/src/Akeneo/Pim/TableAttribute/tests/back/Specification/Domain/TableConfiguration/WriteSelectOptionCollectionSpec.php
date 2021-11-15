<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\TableAttribute\Domain\TableConfiguration;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Event\SelectOptionWasDeleted;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectOptionCollection;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\SelectOptionCode;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\WriteSelectOptionCollection;
use PhpSpec\ObjectBehavior;

class WriteSelectOptionCollectionSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('fromReadSelectOptionCollection', [SelectOptionCollection::fromNormalized([
            [
                'code' => 'salt',
                'labels' => ['en_US' => 'Salt', 'fr_FR' => 'Sel'],
            ],
            [
                'code' => 'pepper',
                'labels' => ['en_US' => 'Pepper', 'fr_FR' => 'Poivre'],
            ],
        ])]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(WriteSelectOptionCollection::class);
    }

    function it_can_be_normalized()
    {
        $this->normalize()->shouldReturn([
            [
                'code' => 'salt',
                'labels' => ['en_US' => 'Salt', 'fr_FR' => 'Sel'],
            ],
            [
                'code' => 'pepper',
                'labels' => ['en_US' => 'Pepper', 'fr_FR' => 'Poivre'],
            ],
        ]);
    }

    function it_returns_option_codes()
    {
        $this->getOptionCodes()->shouldBeLike([
            SelectOptionCode::fromString('salt'),
            SelectOptionCode::fromString('pepper'),
        ]);
    }

    function it_updates_the_collection_and_store_events()
    {
        $this->update('nutrition', ColumnCode::fromString('ingredient'), [
            [
                'code' => 'salt',
                'labels' => ['en_US' => 'Salt', 'fr_FR' => 'Sel'],
            ],
            [
                'code' => 'sugar',
                'labels' => ['en_US' => 'Sugar', 'fr_FR' => 'Sucre'],
            ],
        ]);
        $this->normalize()->shouldReturn([
            [
                'code' => 'salt',
                'labels' => ['en_US' => 'Salt', 'fr_FR' => 'Sel'],
            ],
            [
                'code' => 'sugar',
                'labels' => ['en_US' => 'Sugar', 'fr_FR' => 'Sucre'],
            ],
        ]);
        $this->getOptionCodes()->shouldBeLike([
            SelectOptionCode::fromString('salt'),
            SelectOptionCode::fromString('sugar'),
        ]);
        $this->releaseEvents()->shouldBeLike([
            new SelectOptionWasDeleted('nutrition', ColumnCode::fromString('ingredient'), SelectOptionCode::fromString('pepper')),
        ]);
        $this->releaseEvents()->shouldReturn([]);
    }
}
