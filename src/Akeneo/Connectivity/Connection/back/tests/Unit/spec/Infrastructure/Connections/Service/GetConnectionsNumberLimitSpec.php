<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Connections\Service;

use PhpSpec\ObjectBehavior;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetConnectionsNumberLimitSpec extends ObjectBehavior
{
    public function it_returns_limit_through_getter(): void
    {
        $this->beConstructedWith(50);
        $this->getLimit()->shouldBe(50);
    }

    public function it_sets_new_limit(): void
    {
        $this->beConstructedWith(98);
        $this->setLimit(32);
        $this->getLimit()->shouldBe(32);
    }
}
