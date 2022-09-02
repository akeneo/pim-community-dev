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

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\SelectOptionCollectionRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;

final class Attribute implements ArrayConverterInterface
{
    private ArrayConverterInterface $decorated;
    private SelectOptionCollectionRepository $optionCollectionRepository;

    public function __construct(
        ArrayConverterInterface $decorated,
        SelectOptionCollectionRepository $optionCollectionRepository
    ) {
        $this->decorated = $decorated;
        $this->optionCollectionRepository = $optionCollectionRepository;
    }

    public function convert(array $item, array $options = []): array
    {
        $result = $this->decorated->convert($item, $options);

        if ($item['type'] !== AttributeTypes::TABLE) {
            return $result;
        }

        $tableConfiguration = \json_decode($result['table_configuration'], true);

        foreach ($tableConfiguration as $index => $normalizedColumn) {
            if ($normalizedColumn['data_type'] !== SelectColumn::DATATYPE) {
                continue;
            }

            $optionCollection = $this->optionCollectionRepository->getByColumn(
                $item['code'],
                ColumnCode::fromString($normalizedColumn['code'])
            );
            $tableConfiguration[$index]['options'] = $optionCollection->normalize();
            $tableConfiguration[$index]['validations'] = (object) $tableConfiguration[$index]['validations'];
            $tableConfiguration[$index]['labels'] = (object) $tableConfiguration[$index]['labels'];
        }

        $result['table_configuration'] = \json_encode($tableConfiguration);

        return $result;
    }
}
