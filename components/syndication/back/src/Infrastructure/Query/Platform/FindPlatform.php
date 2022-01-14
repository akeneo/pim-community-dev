<?php

namespace Akeneo\Platform\Syndication\Infrastructure\Query\Platform;

use Akeneo\Platform\Syndication\Domain\Query\Platform\FindPlatformInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Exception;

class FindPlatform implements FindPlatformInterface
{
    private Connection $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    /**
     * {@inheritDoc}
     */
    public function byCode(string $platformCode): array
    {
        // There is probably a better way to do this but it works and is pretty efficient
        $sql = <<<SQL
            SELECT
                platform.code as code,
                platform.label as label,
                JSON_ARRAYAGG(JSON_OBJECT(
                    'code', family.code,
                    'label', family.label
                ))
                 as families
            FROM akeneo_syndication_connected_channel AS platform
            JOIN akeneo_syndication_family AS family on family.connected_channel_code = platform.code
            GROUP BY platform.code
            HAVING platform.code = :connected_channel_code;
        SQL;

        $statement = $this->connection->executeQuery(
            $sql,
            [
                'connected_channel_code' => $platformCode
            ],
            [
                'connected_channel_code' => Types::STRING
            ]
        );

        $results = $statement->fetchAllAssociative();

        if (count($results) === 0) {
            throw new Exception(sprintf('Platform "%s" not found', $platformCode));
        }

        $result = $results[0];

        return [
            'code' => $result['code'],
            'label' => $result['label'],
            'families' => json_decode($result['families'], true)
        ];
    }
}
