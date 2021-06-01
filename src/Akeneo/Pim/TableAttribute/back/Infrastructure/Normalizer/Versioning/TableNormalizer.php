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

namespace Akeneo\Pim\TableAttribute\Infrastructure\Normalizer\Versioning;

use Akeneo\Pim\TableAttribute\Domain\Value\Table;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Webmozart\Assert\Assert;

final class TableNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /**
     * {@inheritDoc}
     */
    public function normalize($object, string $format = null, array $context = []): array
    {
        Assert::isInstanceOf($object, Table::class);

        return $object->normalize();
    }

    /**
     * {@inheritDoc}
     */
    public function supportsNormalization($data, string $format = null): bool
    {
        return $data instanceof Table && in_array($format, ['flat', 'versioning']);
    }

    /**
     * {@inheritDoc}
     */
    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
