<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Pim\Enrichment\Bundle\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Event\ProductModelsWereCreatedOrUpdated;
use Akeneo\Pim\Enrichment\Component\Product\Event\ProductModelWasCreated;
use Akeneo\Pim\Enrichment\Component\Product\Event\ProductModelWasUpdated;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Webmozart\Assert\Assert;

final class ProductModelsWereCreatedOrUpdatedNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * {@inheritDoc}
     */
    public function denormalize($data, string $type, string $format = null, array $context = []): ProductModelsWereCreatedOrUpdated
    {
        Assert::keyExists($data, 'events', 'Normalized ProductModelsWereUpdatedMessage must contains a key "events"');
        Assert::isArray($data['events'], 'Normalized ProductModelsWereUpdatedMessage events property must be an array');

        $events = [];
        foreach ($data['events'] as $normalizedEvent) {
            Assert::isArray($normalizedEvent, 'Normalized event must be an array');
            Assert::keyExists($normalizedEvent, 'product_model_id', 'Normalized event must contains a key "product_model_id"');
            Assert::string($normalizedEvent['product_model_id'], 'Normalized event product_model_id property must be a string');
            if (\array_key_exists('created_at', $normalizedEvent)) {
                $events[] = new ProductModelWasCreated(
                    $normalizedEvent['product_model_id'],
                    \DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, $normalizedEvent['created_at'])
                );
            } elseif (\array_key_exists('updated_at', $normalizedEvent)) {
                $events[] = new ProductModelWasUpdated(
                    $normalizedEvent['product_model_id'],
                    \DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, $normalizedEvent['updated_at'])
                );
            } else {
                throw new \InvalidArgumentException('Normalized event should have a "created_at" or "updated_at" key');
            }
        }

        return new ProductModelsWereCreatedOrUpdated($events);
    }

    /**
     * {@inheritDoc}
     */
    public function supportsDenormalization($data, string $type, string $format = null): bool
    {
        return $type === ProductModelsWereCreatedOrUpdated::class;
    }

    /**
     * {@inheritDoc}
     */
    public function normalize($object, string $format = null, array $context = []): array
    {
        Assert::isInstanceOf($object, ProductModelsWereCreatedOrUpdated::class);

        return ['events' =>
            \array_map(
                fn (ProductModelWasCreated|ProductModelWasUpdated $event) => $event instanceof ProductModelWasCreated ? [
                    'product_model_id' => $event->id,
                    'created_at' => $event->createdAt->format(\DateTimeInterface::ATOM),
                ] : [
                    'product_model_id' => $event->id,
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
        return $data instanceof ProductModelsWereCreatedOrUpdated;
    }
}
