<?php

namespace Akeneo\CoEdition\Application\Storage;

use Akeneo\CoEdition\Domain\Room;
use Akeneo\CoEdition\Domain\ValueObject\RoomUuid;

interface FindRoom
{
    public function __invoke(RoomUuid $roomUuid): ?Room;
}
