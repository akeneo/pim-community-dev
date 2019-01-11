<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\ValueObject;

use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\ValueObject\WarningCollection;
use PhpSpec\ObjectBehavior;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class WarningCollectionSpec extends ObjectBehavior
{
    public function it_is_a_warning_collection(): void
    {
        $this->beConstructedwith(
            [
                '_embedded' => [
                    'warnings' => [],
                ],
            ]
        );
        $this->shouldHaveType(WarningCollection::class);
    }

    public function it_provides_warning_messages_indexed_by_tracker_id(): void
    {
        $this->beConstructedWith(
            [
                '_embedded' => [
                    'warnings' => [
                        [
                            'message' => 'warning message 1',
                            'entry' => [
                                'tracker_id' => '44',
                            ],
                        ],
                        [
                            'message' => 'warning message 2',
                            'entry' => [
                                'tracker_id' => '56',
                            ],
                        ],
                    ],
                ],
            ]
        );

        $this->toArray()->shouldReturn(
            [
                44 => 'warning message 1',
                56 => 'warning message 2',
            ]
        );
    }
}
