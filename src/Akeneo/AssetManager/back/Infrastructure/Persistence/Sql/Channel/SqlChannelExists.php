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

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\Channel;

use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Query\Channel\ChannelExistsInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlChannelExists implements ChannelExistsInterface
{
    public function __construct(private Connection $sqlConnection)
    {
    }

    public function exists(ChannelIdentifier $channelIdentifier): bool
    {
        $query = <<<SQL
          SELECT EXISTS(
              SELECT 1 FROM pim_catalog_channel WHERE code = :channel_code
          ) as is_existing
SQL;

        $statement = $this->sqlConnection->executeQuery($query, [
            'channel_code' => $channelIdentifier->normalize()
        ]);

        $platform = $this->sqlConnection->getDatabasePlatform();
        $result = $statement->fetchAssociative();

        return Type::getType(Types::BOOLEAN)->convertToPhpValue($result['is_existing'], $platform);
    }
}
