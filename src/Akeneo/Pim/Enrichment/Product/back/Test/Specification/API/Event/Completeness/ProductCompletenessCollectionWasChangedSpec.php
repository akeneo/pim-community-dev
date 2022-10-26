<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\API\Event\Completeness;

use Akeneo\Pim\Enrichment\Product\API\Event\Completeness\ChangedProductCompleteness;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductUuid;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductCompletenessCollectionWasChangedSpec extends ObjectBehavior
{
    public function it_cant_be_created_with_invalid_changed_product_completeness()
    {
        $this->beConstructedWith(
            ProductUuid::fromUuid(Uuid::uuid4()),
            new \DateTimeImmutable(),
            [
                new ChangedProductCompleteness('ecommerce', 'en_US', 10, 10, 1, 0, 90, 100),
                'invalid_changed_product_completeness',
            ]
        );
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
