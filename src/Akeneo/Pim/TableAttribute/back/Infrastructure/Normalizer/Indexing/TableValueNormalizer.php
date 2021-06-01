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

namespace Akeneo\Pim\TableAttribute\Infrastructure\Normalizer\Indexing;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\AbstractProductValueNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\ValueCollectionNormalizer;
use Akeneo\Pim\TableAttribute\Infrastructure\Value\TableValue;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Webmozart\Assert\Assert;

final class TableValueNormalizer extends AbstractProductValueNormalizer implements CacheableSupportsMethodInterface
{
    /**
     * {@inheritDoc}
     */
    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof TableValue
            && ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX === $format;
    }

    /**
     * {@inheritDoc}
     */
    protected function getNormalizedData(ValueInterface $value): array
    {
        Assert::isInstanceOf($value, TableValue::class);

        return $value->getData()->normalize();
    }

    /**
     * {@inheritDoc}
     */
    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
