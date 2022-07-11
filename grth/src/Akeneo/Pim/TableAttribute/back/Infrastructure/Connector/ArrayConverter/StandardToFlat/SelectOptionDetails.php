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

namespace Akeneo\Pim\TableAttribute\Infrastructure\Connector\ArrayConverter\StandardToFlat;

use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;

final class SelectOptionDetails implements ArrayConverterInterface
{
    public function convert(array $item, array $options = []): array
    {
        $converted = [];
        foreach ($item as $property => $data) {
            switch ($property) {
                case 'attribute':
                case 'column':
                case 'code':
                    $converted[$property] = $data;
                    break;
                case 'labels':
                    foreach ($data as $localeCode => $label) {
                        $converted[\sprintf('label-%s', $localeCode)] = $label;
                    }
                break;
                default:
                    break;
            }
        }

        return $converted;
    }
}
