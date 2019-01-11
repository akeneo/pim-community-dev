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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api;

use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\ApiResponse;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\ValueObject\SubscriptionCollection;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\ValueObject\WarningCollection;
use PhpSpec\ObjectBehavior;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class ApiResponseSpec extends ObjectBehavior
{
    public function let(SubscriptionCollection $subscriptionCollection): void
    {
        $warnings = new WarningCollection([
            '_embedded' => [
                'warnings' => [],
            ],
        ]);
        $this->beConstructedWith($subscriptionCollection, $warnings);
    }

    public function it_is_an_api_response(): void
    {
        $this->shouldHaveType(ApiResponse::class);
    }

    public function it_can_check_if_there_is_no_warning(): void
    {
        $this->hasWarnings()->shouldReturn(false);
    }

    public function it_can_check_if_there_are_warnings(SubscriptionCollection $subscriptionCollection, $warnings): void
    {
        $warnings = new WarningCollection([
            '_embedded' => [
                'warnings' => [
                    [
                        'message' => 'warning message 1',
                        'entry' => [
                            'tracker_id' => '44',
                        ],
                    ],
                ],
            ],
        ]);
        $this->beConstructedWith($subscriptionCollection, $warnings);
        $this->hasWarnings()->shouldReturn(true);
    }
}
