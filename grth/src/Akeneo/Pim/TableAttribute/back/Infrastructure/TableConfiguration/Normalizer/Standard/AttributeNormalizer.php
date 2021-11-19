<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Infrastructure\TableConfiguration\Normalizer\Standard;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AttributeNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    private NormalizerInterface $baseAttributeNormalizer;

    public function __construct(NormalizerInterface $baseAttributeNormalizer)
    {
        $this->baseAttributeNormalizer = $baseAttributeNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, string $format = null, array $context = []): array
    {
        $normalized = $this->baseAttributeNormalizer->normalize($object, $format, $context);
        if ($object instanceof AttributeInterface && $object->getType() === AttributeTypes::TABLE) {
            $normalized['table_configuration'] = $object->getRawTableConfiguration();

            if (null !== $normalized['table_configuration']) {
                foreach (\array_keys($normalized['table_configuration']) as $index) {
                    unset($normalized['table_configuration'][$index]['id']);
                }
            }
        }

        return $normalized;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, string $format = null): bool
    {
        return $this->baseAttributeNormalizer->supportsNormalization($data, $format);
    }

    /**
     * {@inheritdoc}
     */
    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
