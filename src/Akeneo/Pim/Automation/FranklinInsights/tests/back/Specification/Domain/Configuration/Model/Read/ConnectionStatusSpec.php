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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Model\Read;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Model\Read\ConnectionStatus;
use PhpSpec\ObjectBehavior;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class ConnectionStatusSpec extends ObjectBehavior
{
    public function it_is_a_franklin_insights_connection_status(): void
    {
        $this->beConstructedWith(true, true, 0);
        $this->shouldBeAnInstanceOf(ConnectionStatus::class);
    }

    public function it_returns_if_the_connection_status_is_active(): void
    {
        $this->beConstructedWith(true, true, 0);
        $this->isActive()->shouldReturn(true);
    }

    public function it_returns_if_the_connection_status_is_not_active(): void
    {
        $this->beConstructedWith(false, true, 0);
        $this->isActive()->shouldReturn(false);
    }

    public function it_returns_if_the_identifiers_mapping_is_valid(): void
    {
        $this->beConstructedWith(true, true, 0);
        $this->isIdentifiersMappingValid()->shouldReturn(true);
    }

    public function it_returns_if_the_identifiers_mapping_is_not_valid(): void
    {
        $this->beConstructedWith(true, false, 0);
        $this->isIdentifiersMappingValid()->shouldReturn(false);
    }

    public function it_returns_count(): void
    {
        $this->beConstructedWith(true, false, 42);
        $this->productSubscriptionCount()->shouldReturn(42);
    }
}
