<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Webhook\EventNormalizer;

use Akeneo\Pim\Enrichment\Component\Product\Message\ProductCreated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductRemoved;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductUpdated;
use Akeneo\Platform\Component\EventQueue\EventInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductEventNormalizer implements EventNormalizerInterface
{
    public function supports(EventInterface $event): bool
    {
        return $event instanceof ProductCreated
            || $event instanceof ProductUpdated
            || $event instanceof ProductRemoved;
    }

    /**
     * @param ProductCreated|ProductUpdated|ProductRemoved $event
     *
     * @return array<mixed>
     */
    public function normalize(EventInterface $event): array
    {
        $normalizer = new EventNormalizer();

        return $normalizer->normalize($event) + [
            'product_identifier' => $event->getIdentifier()
        ];
    }
}
