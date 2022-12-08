<?php

namespace Akeneo\CoEdition\Test\Integration\Infrastructure\Storage\SQL;

use Akeneo\CoEdition\Application\Storage\FindRoom;
use Akeneo\CoEdition\Domain\Room;
use Akeneo\CoEdition\Domain\ValueObject\EditorToken;
use Akeneo\CoEdition\Domain\ValueObject\RoomUuid;
use Akeneo\CoEdition\Infrastructure\Storage\SQL\SQLFindRoom;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Types\Types;

class SQLFindRoomTest extends TestCase
{
    private readonly SQLFindRoom $findRoom;

    public function setUp(): void
    {
        $this->findRoom = new SQLFindRoom(
            $this->get('database_connection')
        );
    }

    public function test_it_returns_the_room(): void
    {
        $roomUuid = RoomUuid::create();

        $query = <<<SQL
INSERT INTO akeneo_coedition_room (uuid, editors, created, updated)
VALUES (:uuid, :editors,  UTC_TIMESTAMP(),  UTC_TIMESTAMP())
SQL;

        $this->get('database_connection')->executeQuery(
            $query,
            [
                'uuid' => $roomUuid->toBytes(),
                'editors' => [],
            ],
            [

                'uuid' => Types::STRING,
                'editors' => Types::JSON,
            ]
        );

        $room = ($this->findRoom)($roomUuid);

        $this->assertInstanceOf(Room::class, $room);
        $this->assertEquals((string) $roomUuid, (string) $room->getRoomUuid());
    }

    public function test_it_returns_the_room_with_editors(): void
    {
        $token = EditorToken::fromString(\bin2hex(\random_bytes(20)));
        $roomUuid = RoomUuid::create();

        $query = <<<SQL
INSERT INTO akeneo_coedition_room (uuid, editors, created, updated)
VALUES (:uuid, :editors,  UTC_TIMESTAMP(),  UTC_TIMESTAMP())
SQL;

        $this->get('database_connection')->executeQuery(
            $query,
            [
                'uuid' => $roomUuid->toBytes(),
                'editors' => [
                    [
                        'token' => (string) $token,
                        'name' => 'an editor',
                        'avatar' => '',
                    ],
                ],
            ],
            [

                'uuid' => Types::STRING,
                'editors' => Types::JSON,
            ]
        );

        $room = ($this->findRoom)($roomUuid);

        $this->assertInstanceOf(Room::class, $room);
        $this->assertEquals((string) $roomUuid, (string) $room->getRoomUuid());
        $this->assertCount(1, $room->getEditors());
    }

    public function test_it_returns_null_when_the_room_does_not_exist(): void
    {
        $roomUuid = RoomUuid::create();

        $room = ($this->findRoom)($roomUuid);

        $this->assertNull($room);

    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
