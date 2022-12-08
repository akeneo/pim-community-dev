<?php

namespace Akeneo\CoEdition\Test\Unit\Application;

use Akeneo\CoEdition\Application\Builder\EditorBuilder;
use Akeneo\CoEdition\Application\Builder\RoomBuilder;
use Akeneo\CoEdition\Application\Exception\RoomNotFoundException;
use Akeneo\CoEdition\Application\LeaveRoom;
use Akeneo\CoEdition\Application\Storage\FindRoom;
use Akeneo\CoEdition\Application\Storage\StoreRoom;
use Akeneo\CoEdition\Domain\Editor;
use Akeneo\CoEdition\Domain\ValueObject\EditorToken;
use Akeneo\CoEdition\Domain\ValueObject\RoomUuid;
use PHPUnit\Framework\TestCase;

class LeaveRoomTest extends TestCase
{
    private FindRoom $findRoom;
    private StoreRoom $storeRoom;
    private LeaveRoom $leaveRoom;

    public function setUp(): void
    {
        $this->findRoom = $this->createMock(FindRoom::class);
        $this->storeRoom = $this->createMock(StoreRoom::class);
        $this->leaveRoom = new LeaveRoom(
            findRoom: $this->findRoom,
            storeRoom: $this->storeRoom,
        );
    }

    public function test_it_unregisters_the_editor_from_the_room(): void
    {
        $token = EditorToken::fromString(\bin2hex(\random_bytes(20)));
        $editor = (new EditorBuilder())
            ->withToken($token)
            ->build();

        $roomUuid = RoomUuid::create();
        $foundRoom = (new RoomBuilder())
            ->withUuid($roomUuid)
            ->withEditor($editor)
            ->build();
        $updatedRoom = (new RoomBuilder())
            ->withUuid($roomUuid)
            ->build();

        $this->findRoom
            ->expects($this->once())
            ->method('__invoke')
            ->with($roomUuid)
            ->willReturn($foundRoom);

        $this->storeRoom
            ->expects($this->once())
            ->method('__invoke')
            ->with($updatedRoom);

        $room = ($this->leaveRoom)($roomUuid, $editor);

        $this->assertCount(0, array_filter(
            $room->getEditors(), fn (Editor $e) => $e->getToken() === $editor->getToken()
        ));

        $this->assertEquals((string) $roomUuid, (string) $room->getRoomUuid());
    }

    public function test_it_throw_exception_when_the_room_does_not_exist(): void
    {
        $token = EditorToken::fromString(\bin2hex(\random_bytes(20)));
        $editor = (new EditorBuilder())
            ->withToken($token)
            ->build();

        $roomUuid = RoomUuid::create();

        $this->findRoom
            ->method('__invoke')
            ->with($roomUuid)
            ->willReturn(null);

        $this->expectException(RoomNotFoundException::class);

        ($this->leaveRoom)($roomUuid, $editor);
    }


}
