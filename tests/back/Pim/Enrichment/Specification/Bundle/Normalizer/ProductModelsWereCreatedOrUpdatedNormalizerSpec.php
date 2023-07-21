<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


namespace Specification\Akeneo\Pim\Enrichment\Bundle\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Event\ProductModelsWereCreatedOrUpdated;
use Akeneo\Pim\Enrichment\Component\Product\Event\ProductModelWasCreated;
use Akeneo\Pim\Enrichment\Component\Product\Event\ProductModelWasUpdated;
use PhpSpec\ObjectBehavior;

class ProductModelsWereCreatedOrUpdatedNormalizerSpec extends ObjectBehavior
{
    function it_supports_product_models_were_created_or_updated_objects_for_normalization()
    {
        $object = new ProductModelsWereCreatedOrUpdated([
            new ProductModelWasCreated(1, new \DateTimeImmutable()),
        ]);

        $this->supportsNormalization($object)->shouldReturn(true);
        $this->supportsNormalization(new \stdClass())->shouldReturn(false);
    }

    function it_supports_product_models_were_created_or_updated_objects_for_denormalization()
    {
        $this->supportsDenormalization(null, ProductModelsWereCreatedOrUpdated::class)->shouldReturn(true);
        $this->supportsDenormalization(null, 'Other')->shouldReturn(false);
    }

    function it_normalizes_product_models_were_created_or_updated_object()
    {
        $object = new ProductModelsWereCreatedOrUpdated([
            new ProductModelWasCreated(1, \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2023-06-26 02:05:27')),
            new ProductModelWasUpdated(2, \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2023-06-27 02:05:27')),
        ]);

        $this->normalize($object)->shouldReturn([
            'events' => [
                ['product_model_id' => 1, 'created_at' => '2023-06-26T02:05:27+00:00'],
                ['product_model_id' => 2, 'updated_at' => '2023-06-27T02:05:27+00:00'],
            ],
        ]);
    }

    function it_denormalizes_product_models_were_created_or_updated_object()
    {
        $object = new ProductModelsWereCreatedOrUpdated([
            new ProductModelWasCreated(1, \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2023-06-26 02:05:27')),
            new ProductModelWasUpdated(2, \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2023-06-27 02:05:27')),
        ]);

        $normalized = [
            'events' => [
                ['product_model_id' => 1, 'created_at' => '2023-06-26T02:05:27+00:00'],
                ['product_model_id' => 2, 'updated_at' => '2023-06-27T02:05:27+00:00'],
            ],
        ];

        $this->denormalize($normalized, ProductModelsWereCreatedOrUpdated::class)->shouldBeLike($object);
    }
}
