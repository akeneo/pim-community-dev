<?php

namespace Akeneo\CoEdition\Test\Integration\Infrastructure\Storage\SQL;

use Akeneo\CoEdition\Application\Builder\EditorBuilder;
use Akeneo\CoEdition\Application\Builder\RoomBuilder;
use Akeneo\CoEdition\Domain\Room;
use Akeneo\CoEdition\Domain\ValueObject\EditorToken;
use Akeneo\CoEdition\Domain\ValueObject\RoomUuid;
use Akeneo\CoEdition\Infrastructure\Storage\SQL\SQLFindRoom;
use Akeneo\CoEdition\Infrastructure\Storage\SQL\SQLStoreRoom;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

class SQLStoreRoomTest extends TestCase
{
    private SQLStoreRoom $storeRoom;
    private SQLFindRoom $findRoom;

    public function setUp(): void
    {
        $this->findRoom = new SQLFindRoom(
            $this->get('database_connection')
        );

        $this->storeRoom = new SQLStoreRoom(
            $this->get('database_connection')
        );
    }

    public function test_it_stores_the_room(): void
    {
        $roomUuid = RoomUuid::create();
        $room = (new RoomBuilder())
            ->withUuid($roomUuid)
            ->build();

        ($this->storeRoom)($room);

        $storedRoom = ($this->findRoom)($roomUuid);
        $this->assertInstanceOf(Room::class, $storedRoom);
        $this->assertEquals((string) $roomUuid, (string) $storedRoom->getRoomUuid());

    }

    public function test_it_stores_the_room_with_editors(): void
    {
        $token = EditorToken::fromString(\bin2hex(\random_bytes(20)));
        $editor = (new EditorBuilder())
            ->withToken($token)
            ->build();

        $roomUuid = RoomUuid::create();
        $room = (new RoomBuilder())
            ->withUuid($roomUuid)
            ->withEditor($editor)
            ->build();

        ($this->storeRoom)($room);

        $storedRoom = ($this->findRoom)($roomUuid);
        $this->assertInstanceOf(Room::class, $storedRoom);
        $this->assertEquals((string) $roomUuid, (string) $storedRoom->getRoomUuid());
        $this->assertCount(1, $storedRoom->getEditors());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

}
