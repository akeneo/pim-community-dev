<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\API\Event\Completeness;

use Akeneo\Pim\Enrichment\Product\API\Event\Completeness\ProductWasCompletedOnChannelLocale;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductUuid;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductWasCompletedOnChannelLocaleCollectionSpec extends ObjectBehavior
{
    public function it_cant_be_created_empty()
    {
        $this->beConstructedWith([]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_cant_be_created_with_invalid_products_completeness()
    {
        $this->beConstructedWith(['completeness1','completeness2']);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_returns_the_events()
    {
        $events = [
            new ProductWasCompletedOnChannelLocale(ProductUuid::fromUuid(Uuid::uuid4()), new \DateTimeImmutable(), 'ecormmerce', 'en_US','1'),
            new ProductWasCompletedOnChannelLocale(ProductUuid::fromUuid(Uuid::uuid4()), new \DateTimeImmutable(), 'ecormmerce', 'fr_FR', null),
        ];
        $this->beConstructedWith($events);

        $this->all()->shouldReturn($events);
    }
}
