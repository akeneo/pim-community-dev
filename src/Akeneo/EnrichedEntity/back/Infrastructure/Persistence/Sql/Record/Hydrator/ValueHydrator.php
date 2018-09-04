<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\EnrichedEntity\Infrastructure\Persistence\Sql\Record\Hydrator;

use Akeneo\EnrichedEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\LocaleReference;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\Value;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class ValueHydrator implements ValueHydratorInterface
{
    /** @var AbstractPlatform */
    private $platform;

    /** @var DataHydratorRegistry */
    private $dataHydratorRegistry;

    public function __construct(
        Connection $sqlConnection,
        DataHydratorRegistry $dataHydratorRegistry
    ) {
        $this->platform = $sqlConnection->getDatabasePlatform();
        $this->dataHydratorRegistry = $dataHydratorRegistry;
    }

    public function hydrate(array $row, AbstractAttribute $attribute): Value
    {
        $this->checkRowKeys($row);
        $dataHydrator = $this->dataHydratorRegistry->getHydrator($attribute);
        $data = $dataHydrator->hydrate($row['data'], $attribute);

        return Value::create(
            $attribute->getIdentifier(),
            $this->getChannelReference($row),
            $this->getLocaleReference($row),
            $data
        );
    }

    private function checkRowKeys($row): void
    {
        if (!array_key_exists('data', $row) ||
            !array_key_exists('channel', $row) ||
            !array_key_exists('locale', $row)
        ) {
            throw new \RuntimeException('Cannot hydrate the value because either the channel, locale or data is missing');
        }
    }

    private function getChannelReference(array $row): ChannelReference
    {
        return null === $row['channel'] ? ChannelReference::noReference() : ChannelReference::fromChannelCode($row['channel']);
    }

    private function getLocaleReference(array $row): LocaleReference
    {
        return null === $row['locale'] ? LocaleReference::noReference() : LocaleReference::fromLocaleCode($row['locale']);
    }
}
