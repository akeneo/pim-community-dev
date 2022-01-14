<?php

namespace Akeneo\Platform\Syndication\Infrastructure\Query\Platform;

use Akeneo\Platform\Syndication\Domain\Query\Platform\FindPlatformFamilyInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Exception;

class FindPlatformFamily implements FindPlatformFamilyInterface
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
    public function byPlatformCodeAndFamilyCode(string $platformCode, string $familyCode): array
    {
        $sql = <<<SQL
            SELECT family.code as code, family.label as label, family.requirements as requirements
            FROM akeneo_syndication_family AS family
            WHERE family.code = :family_code AND family.connected_channel_code = :connected_channel_code;
        SQL;

        $statement = $this->connection->executeQuery(
            $sql,
            [
                'connected_channel_code' => $platformCode,
                'family_code' => $familyCode
            ],
            [
                'connected_channel_code' => Types::STRING,
                'family_code' => Types::STRING
            ]
        );

        $results = $statement->fetchAllAssociative();

        if (count($results) === 0) {
            throw new Exception(sprintf('Platform "%s" not found', $platformCode));
        }

        $result = $results[0];

        $family = [
            'code' => $result['code'],
            'label' => $result['label'],
            'requirements' => json_decode($result['requirements'], true)
        ];

        return $family;
    }
}
