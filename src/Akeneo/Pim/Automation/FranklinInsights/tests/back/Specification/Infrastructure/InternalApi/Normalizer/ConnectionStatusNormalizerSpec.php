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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\InternalApi\Normalizer;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Model\Read\ConnectionStatus;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\InternalApi\Normalizer\ConnectionStatusNormalizer;
use PhpSpec\ObjectBehavior;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class ConnectionStatusNormalizerSpec extends ObjectBehavior
{
    public function it_is_a_connection_status_normalizer(): void
    {
        $this->shouldBeAnInstanceOf(ConnectionStatusNormalizer::class);
    }

    public function it_normalizes_a_connection_status(): void
    {
        $connectionStatus = new ConnectionStatus(true, true, true, 42);

        $this->normalize($connectionStatus)->shouldReturn([
            'isActive' => true,
            'isValid' => true,
            'isIdentifiersMappingValid' => true,
            'productSubscriptionCount' => 42,
        ]);

        $anotherConnectionStatus = new ConnectionStatus(false, false, false, 42);

        $this->normalize($anotherConnectionStatus)->shouldReturn([
            'isActive' => false,
            'isValid' => false,
            'isIdentifiersMappingValid' => false,
            'productSubscriptionCount' => 42,
        ]);
    }
}
