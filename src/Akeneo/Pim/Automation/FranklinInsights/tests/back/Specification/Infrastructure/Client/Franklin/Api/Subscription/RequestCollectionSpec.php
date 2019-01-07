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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\Subscription;

use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\Subscription\Request;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\Subscription\RequestCollection;
use PhpSpec\ObjectBehavior;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class RequestCollectionSpec extends ObjectBehavior
{
    public function it_is_a_request_collection(): void
    {
        $this->shouldHaveType(RequestCollection::class);
    }

    public function it_can_add_requests(): void
    {
        $this->toFormParams()->shouldReturn([]);
        $this->add(new Request(['asin' => '123456'], 42, ['code' => 'laptop']))->shouldReturn(null);
        $this->toFormParams()->shouldReturn(
            [
                [
                    'asin' => '123456',
                    'tracker_id' => 42,
                    'family' => [
                        'code' => 'laptop',
                    ],
                ],
            ]
        );
    }

    public function it_provides_form_params(): void
    {
        $this->add(new Request(['asin' => 'ABC123'], 42, ['code' => 'laptop']));
        $this->add(new Request(['upc' => '123456'], 50, ['code' => 'router']));

        $this->toFormParams()->shouldReturn(
            [
                [
                    'asin' => 'ABC123',
                    'tracker_id' => 42,
                    'family' => [
                        'code' => 'laptop',
                    ],
                ],
                [
                    'upc' => '123456',
                    'tracker_id' => 50,
                    'family' => [
                        'code' => 'router',
                    ],
                ],
            ]
        );
    }
}
