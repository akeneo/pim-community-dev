<?php

namespace Akeneo\CoEdition\Application;

use Akeneo\CoEdition\Application\Exception\RoomNotFoundException;
use Akeneo\CoEdition\Application\Storage\FindRoom;
use Akeneo\CoEdition\Application\Storage\StoreRoom;
use Akeneo\CoEdition\Domain\Editor;
use Akeneo\CoEdition\Domain\Room;
use Akeneo\CoEdition\Domain\ValueObject\RoomUuid;

class LeaveRoom
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
            throw new RoomNotFoundException(sprintf('Room with identifier "%s" is not found', (string) $roomUuid));
        }

        $room->leave($editor);

        ($this->storeRoom)($room);

        // @todo notify the event

        return $room;
    }
}
