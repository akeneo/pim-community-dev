<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Infrastructure\Normalizer;

use Akeneo\Pim\Enrichment\Product\API\Event\ProductsWereCreatedOrUpdated;
use Akeneo\Pim\Enrichment\Product\API\Event\ProductWasCreated;
use Akeneo\Pim\Enrichment\Product\API\Event\ProductWasUpdated;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductsWereCreatedOrUpdatedNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * {@inheritDoc}
     */
    public function denormalize($data, string $type, string $format = null, array $context = []): ProductsWereCreatedOrUpdated
    {
        Assert::keyExists($data, 'events', 'Normalized ProductsWereUpdatedMessage must contains a key "events"');
        Assert::isArray($data['events'], 'Normalized ProductsWereUpdatedMessage events property must be an array');

        $events = [];
        foreach ($data['events'] as $normalizedEvent) {
            Assert::isArray($normalizedEvent, 'Normalized event must be an array');
            Assert::keyExists($normalizedEvent, 'product_uuid', 'Normalized event must contains a key "product_uuid"');
            Assert::string($normalizedEvent['product_uuid'], 'Normalized event product_uuid property must be a string');
            if (\array_key_exists('created_at', $normalizedEvent)) {
                $events[] = new ProductWasCreated(
                    Uuid::fromString($normalizedEvent['product_uuid']),
                    \DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, $normalizedEvent['created_at'])
                );
            } elseif (\array_key_exists('updated_at', $normalizedEvent)) {
                $events[] = new ProductWasUpdated(
                    Uuid::fromString($normalizedEvent['product_uuid']),
                    \DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, $normalizedEvent['updated_at'])
                );
            } else {
                throw new \InvalidArgumentException('Normalized event should have a "created_at" or "updated_at" key');
            }
        }

        return new ProductsWereCreatedOrUpdated($events);
    }

    /**
     * {@inheritDoc}
     */
    public function supportsDenormalization($data, string $type, string $format = null): bool
    {
        return $type === ProductsWereCreatedOrUpdated::class;
    }

    /**
     * {@inheritDoc}
     */
    public function normalize($object, string $format = null, array $context = []): array
    {
        Assert::isInstanceOf($object, ProductsWereCreatedOrUpdated::class);

        return ['events' =>
            \array_map(
                fn (ProductWasCreated|ProductWasUpdated $event) => $event instanceof ProductWasCreated ? [
                    'product_uuid' => $event->productUuid->toString(),
                    'created_at' => $event->createdAt->format(\DateTimeInterface::ATOM),
                ] : [
                    'product_uuid' => $event->productUuid->toString(),
                    'updated_at' => $event->updatedAt->format(\DateTimeInterface::ATOM),
                ],
                $object->events
            )
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function supportsNormalization($data, string $format = null): bool
    {
        return $data instanceof ProductsWereCreatedOrUpdated;
    }
}
