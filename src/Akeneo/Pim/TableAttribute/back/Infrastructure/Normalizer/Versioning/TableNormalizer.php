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

use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Versioning\Product\AbstractValueDataNormalizer;
use Akeneo\Pim\TableAttribute\Domain\Value\Table;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Webmozart\Assert\Assert;

final class TableNormalizer extends AbstractValueDataNormalizer implements CacheableSupportsMethodInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, string $format = null): bool
    {
        return $data instanceof Table && in_array($format, ['flat', 'versioning']);
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function doNormalize($object, $format = null, array $context = []): ?string
    {
        Assert::isInstanceOf($object, Table::class);
        if ([] === $object->uniqueColumnCodes()) {
            return null;
        }

        return \json_encode($object->normalize());
    }
}
