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

namespace Akeneo\Pim\TableAttribute\Infrastructure\Value\ArrayConverter;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter\AbstractValueConverter;

final class StandardToFlat extends AbstractValueConverter
{
    /**
     * {@inheritdoc}
     */
    public function convert($attributeCode, $data): array
    {
        $convertedItem = [];

        foreach ($data as $value) {
            $flatName = $this->columnsResolver->resolveFlatAttributeName(
                $attributeCode,
                $value['locale'],
                $value['scope']
            );

            $convertedItem[$flatName] = \json_encode($value['data'] ?? null);
        }

        return $convertedItem;
    }
}
