<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\LocaleReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\Value;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class ValueHydrator implements ValueHydratorInterface
{
    private DataHydratorRegistry $dataHydratorRegistry;

    public function __construct(
        DataHydratorRegistry $dataHydratorRegistry
    ) {
        $this->dataHydratorRegistry = $dataHydratorRegistry;
    }

    public function hydrate(array $row, AbstractAttribute $attribute): Value
    {
        $this->checkRowKeys($row);
        $dataHydrator = $this->dataHydratorRegistry->getHydrator($attribute);
        $data = $dataHydrator->hydrate($row['data'], $attribute);

        return Value::create(
            $attribute->getIdentifier(),
            ChannelReference::createFromNormalized($row['channel']),
            LocaleReference::createFromNormalized($row['locale']),
            $data
        );
    }

    private function checkRowKeys($row): void
    {
        if (
            !array_key_exists('data', $row) ||
            !array_key_exists('channel', $row) ||
            !array_key_exists('locale', $row)
        ) {
            throw new \RuntimeException('Cannot hydrate the value because either the channel, locale or data is missing');
        }
    }
}
