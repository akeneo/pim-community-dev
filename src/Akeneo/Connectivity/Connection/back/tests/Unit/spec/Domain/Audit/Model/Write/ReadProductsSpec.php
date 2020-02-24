<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\Audit\Model\Write;

use Akeneo\Connectivity\Connection\Domain\Audit\Model\Write\ReadProducts;
use DateTimeImmutable;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ReadProductsSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith('ecommerce', [4, 2, 6], new DateTimeImmutable('2020-02-24 10:07:32'));
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ReadProducts::class);
    }

    public function it_returns_the_connection_code(): void
    {
        $this->connectionCode()
            ->shouldBe('ecommerce');
    }

    public function it_returns_the_product_ids(): void
    {
        $this->productIds()
            ->shouldBe([4, 2, 6]);
    }

    public function it_returns_the_event_datetime(): void
    {
        $this->eventDatetime()
            ->shouldHaveType(\DateTimeInterface::class);
        $this->eventDatetime()
            ->format('Y-m-d H:i:s')->shouldBe('2020-02-24 10:07:32');
    }
}
