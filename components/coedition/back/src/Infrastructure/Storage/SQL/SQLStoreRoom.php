<?php

namespace Akeneo\CoEdition\Infrastructure\Storage\SQL;

use Akeneo\CoEdition\Application\Storage\StoreRoom;
use Akeneo\CoEdition\Domain\Editor;
use Akeneo\CoEdition\Domain\Room;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

class SQLStoreRoom implements StoreRoom
{
    public function __construct(
        private readonly Connection $connection
    )
    {

    }

    public function __invoke(Room $room): void
    {
        $query = <<<SQL
INSERT INTO akeneo_coedition_room (uuid, editors, created, updated)
VALUES (:uuid, :editors,  UTC_TIMESTAMP(),  UTC_TIMESTAMP())
SQL;

        $this->connection->executeQuery(
            $query,
            [
                'uuid' => $room->getRoomUuid()->toBytes(),
                'editors' => array_map(static function(Editor $editor) {
                    return [
                        'token' => (string) $editor->getToken(),
                        'name' => $editor->getName(),
                        'avatar' => $editor->getAvatar(),
                    ];
                }, $room->getEditors()),
            ],
            [

                'uuid' => Types::STRING,
                'editors' => Types::JSON,
            ]
        );
    }
}
