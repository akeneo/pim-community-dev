<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\back\API\Event;

use Akeneo\Pim\Enrichment\Product\API\Event\ProductsWereUpdated;
use Akeneo\Pim\Enrichment\Product\API\Event\ProductWasUpdated;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @todo spec
 */
final class ProductsWereUpdatedNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        Assert::keyExists($data, 'events', 'Normalized ProductsWereUpdatedMessage must contains a key "events"');
        Assert::isArray($data['events'], 'Normalized ProductsWereUpdatedMessage events property must be an array');

        $events = [];
        foreach ($data['events'] as $normalizedProductWasUpdated) {
            Assert::isArray($normalizedProductWasUpdated, 'Normalized ProductWasUpdated must be an array');
            Assert::keyExists($normalizedProductWasUpdated, 'product_uuid', 'Normalized ProductWasUpdated must contains a key "product_uuid"');
            Assert::string($normalizedProductWasUpdated['product_uuid'], 'Normalized ProductWasUpdated product_uuid property must be a string');
            $events[] = new ProductWasUpdated(Uuid::fromString($normalizedProductWasUpdated['product_uuid']));
        }

        return new ProductsWereUpdated($events);
    }

    public function supportsDenormalization($data, string $type, string $format = null)
    {
        return $type === ProductsWereUpdated::class;
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        Assert::isInstanceOf($object, ProductsWereUpdated::class);

        return ['events' =>
            \array_map(
                fn (ProductWasUpdated $productWasUpdated) => [
                    'product_uuid' => $productWasUpdated->productUuid()->toString(),
                ],
                $object->events
            )
        ];
    }

    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof ProductsWereUpdated;
    }
}
