<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\Infrastructure\Normalizer;

use Akeneo\Pim\Enrichment\Product\API\Event\ProductsWereCreatedOrUpdated;
use Akeneo\Pim\Enrichment\Product\API\Event\ProductWasCreated;
use Akeneo\Pim\Enrichment\Product\API\Event\ProductWasUpdated;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;

final class ProductsWereCreatedOrUpdatedNormalizerSpec extends ObjectBehavior
{
    function it_supports_products_were_created_or_updated_objects_for_normalization()
    {
        $object = new ProductsWereCreatedOrUpdated([
            new ProductWasCreated(Uuid::uuid4(), new \DateTimeImmutable()),
        ]);

        $this->supportsNormalization($object)->shouldReturn(true);
        $this->supportsNormalization(new \stdClass())->shouldReturn(false);
    }

    function it_normalizes_products_were_created_or_updated_object()
    {
        $uuid1 = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();
        $object = new ProductsWereCreatedOrUpdated([
            new ProductWasCreated($uuid1, \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2023-06-26 02:05:27')),
            new ProductWasUpdated($uuid2, \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2023-06-27 02:05:27')),
        ]);

        $this->normalize($object)->shouldReturn([
            'events' => [
                ['product_uuid' => $uuid1->toString(), 'created_at' => '2023-06-26T02:05:27+00:00'],
                ['product_uuid' => $uuid2->toString(), 'updated_at' => '2023-06-27T02:05:27+00:00'],
            ],
        ]);
    }

    function it_supports_products_were_created_or_updated_objects_for_denormalization()
    {
        $object = new ProductsWereCreatedOrUpdated([
            new ProductWasCreated(Uuid::uuid4(), new \DateTimeImmutable()),
        ]);

        $this->supportsDenormalization(null, ProductsWereCreatedOrUpdated::class)->shouldReturn(true);
        $this->supportsDenormalization(null, 'Other')->shouldReturn(false);
    }

    function it_denormalizes_products_were_created_or_updated_object()
    {
        $uuid1 = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();
        $object = new ProductsWereCreatedOrUpdated([
            new ProductWasCreated($uuid1, \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2023-06-26 02:05:27')),
            new ProductWasUpdated($uuid2, \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2023-06-27 02:05:27')),
        ]);

        $normalized = [
            'events' => [
                ['product_uuid' => $uuid1->toString(), 'created_at' => '2023-06-26T02:05:27+00:00'],
                ['product_uuid' => $uuid2->toString(), 'updated_at' => '2023-06-27T02:05:27+00:00'],
            ],
        ];

        $this->denormalize($normalized, ProductsWereCreatedOrUpdated::class)->shouldBeLike($object);
    }
}
