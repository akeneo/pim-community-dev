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
use PhpSpec\ObjectBehavior;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class RequestSpec extends ObjectBehavior
{
    public function it_is_a_client_request(): void
    {
        $this->beConstructedWith(['asin' => '123456789ABC'], 42, ['code ' => 'laptop', 'label' => []]);
        $this->shouldHaveType(Request::class);
    }

    public function it_exposes_properties(): void
    {
        $identifiers = [
            'asin' => '123456789ABC',
            'upc' => '123456789012',
        ];
        $trackerId = 42;
        $familyInfos = [
            'code' => 'laptops',
            'label' => [
                'en_US' => 'Laptops',
            ],
        ];
        $this->beConstructedWith($identifiers, $trackerId, $familyInfos);

        $this->identifiers()->shouldReturn($identifiers);
        $this->trackerId()->shouldReturn($trackerId);
        $this->familyInfos()->shouldReturn($familyInfos);
    }
}
