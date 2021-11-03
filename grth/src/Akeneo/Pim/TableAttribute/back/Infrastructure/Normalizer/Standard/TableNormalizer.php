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

namespace Akeneo\Pim\TableAttribute\Infrastructure\Normalizer\Standard;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnId;
use Akeneo\Pim\TableAttribute\Domain\Value\Table;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Webmozart\Assert\Assert;

class TableNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /**
     * {@inheritDoc}
     */
    public function normalize($table, string $format = null, array $context = []): array
    {
        Assert::isInstanceOf($table, Table::class);
        $normalizedTable = $table->normalize();

        foreach ($normalizedTable as $index => $row) {
            foreach ($row as $stringId => $value) {
                $columnId = ColumnId::fromString($stringId);
                unset($normalizedTable[$index][$stringId]);
                $normalizedTable[$index][$columnId->extractColumnCode()->asString()] = $value;
            }
        }

        return $normalizedTable;
    }

    public function supportsNormalization($data, string $format = null): bool
    {
        return 'standard' === $format && $data instanceof Table;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
