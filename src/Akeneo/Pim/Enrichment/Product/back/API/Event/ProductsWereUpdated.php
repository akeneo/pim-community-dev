<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\API\Event;

use Akeneo\Tool\Component\Messenger\NormalizableMessageInterface;
use Akeneo\Tool\Component\Messenger\TraceableMessageInterface;
use Akeneo\Tool\Component\Messenger\TraceableMessageTrait;
use Ramsey\Uuid\Uuid;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductsWereUpdated implements TraceableMessageInterface, NormalizableMessageInterface
{
    use TraceableMessageTrait;

    /**
     * @param ProductWasUpdated[] $events
     */
    public function __construct(
        public readonly array $events,
    ) {
        Assert::notEmpty($this->events);
        Assert::allIsInstanceOf($this->events, ProductWasUpdated::class);
    }

    public function normalize(): array
    {
        return ['events' =>
            \array_map(
                fn (ProductWasUpdated $productWasUpdated) => [
                    'product_uuid' => $productWasUpdated->productUuid()->toString(),
                ],
                $this->events
            )
        ];
    }

    public static function denormalize(array $normalized): NormalizableMessageInterface
    {
        Assert::keyExists($normalized, 'events', 'Normalized ProductsWereUpdatedMessage must contains a key "events"');
        Assert::isArray($normalized['events'], 'Normalized ProductsWereUpdatedMessage events property must be an array');

        $events = [];
        foreach ($normalized['events'] as $normalizedProductWasUpdated) {
            Assert::isArray($normalizedProductWasUpdated, 'Normalized ProductWasUpdated must be an array');
            Assert::keyExists($normalizedProductWasUpdated, 'product_uuid', 'Normalized ProductWasUpdated must contains a key "product_uuid"');
            Assert::string($normalizedProductWasUpdated['product_uuid'], 'Normalized ProductWasUpdated product_uuid property must be a string');
            $events[] = new ProductWasUpdated(Uuid::fromString($normalizedProductWasUpdated['product_uuid']));
        }

        return new self($events);
    }
}
