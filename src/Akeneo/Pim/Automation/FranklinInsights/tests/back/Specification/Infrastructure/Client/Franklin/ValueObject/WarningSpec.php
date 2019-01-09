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

use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\ValueObject\Warning;
use PhpSpec\ObjectBehavior;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class WarningSpec extends ObjectBehavior
{
    public function it_is_a_warning(): void
    {
        $rawWarning = [
            'message' => 'Lorem ipsum',
            'entry' => [
                'tracker_id' => '42',
            ],
        ];
        $this->beConstructedWith($rawWarning);
        $this->shouldHaveType(Warning::class);
    }

    public function it_throws_an_exception_if_message_key_is_missing(): void
    {
        $this->beConstructedWith([]);
        $this
            ->shouldThrow(new \InvalidArgumentException(sprintf('Missing key "%s" in raw warning data', 'message')))
            ->duringInstantiation();
    }

    public function it_throws_an_exception_if_tracker_id_key_is_missing(): void
    {
        $this->beConstructedWith(
            [
                'message' => 'Lorem ipsum',
                'entry' => [
                ],
            ]
        );
        $this
            ->shouldThrow(new \InvalidArgumentException('Missing "tracker_id" in raw warning data'))
            ->duringInstantiation();
    }

    public function it_exposes_the_message(): void
    {
        $message = 'Lorem ipsum dolor sit amet';
        $this->beConstructedWith(
            [
                'message' => $message,
                'entry' => [
                    'tracker_id' => '42',
                ],
            ]
        );
        $this->message()->shouldReturn($message);
    }

    public function it_returns_tracker_id_as_an_integer(): void
    {
        $this->beConstructedWith(
            [
                'message' => 'Warning message',
                'entry' => [
                    'tracker_id' => '42',
                ],
            ]
        );
        $this->trackerId()->shouldReturn(42);
    }
}
