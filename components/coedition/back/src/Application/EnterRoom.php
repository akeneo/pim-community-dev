<?php

namespace Akeneo\CoEdition\Application;

use Akeneo\CoEdition\Application\Builder\RoomBuilder;
use Akeneo\CoEdition\Application\Storage\FindRoom;
use Akeneo\CoEdition\Application\Storage\StoreRoom;
use Akeneo\CoEdition\Domain\Editor;
use Akeneo\CoEdition\Domain\Room;
use Akeneo\CoEdition\Domain\ValueObject\RoomUuid;

class EnterRoom
{
    public function __construct(
        private readonly FindRoom $findRoom,
        private readonly StoreRoom $storeRoom,
    )
    {

    }

    public function __invoke(RoomUuid $roomUuid, Editor $editor): Room
    {
        $room = ($this->findRoom)($roomUuid);
        if ($room === null) {
            $room = (new RoomBuilder())
                ->withUuid($roomUuid)
                ->build();
        }

        $room->enter($editor);

        ($this->storeRoom)($room);

        // @todo notify the event

        return $room;
    }
}
