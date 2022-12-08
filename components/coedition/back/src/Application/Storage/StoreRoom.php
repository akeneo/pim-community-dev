<?php

namespace Akeneo\CoEdition\Application\Storage;

use Akeneo\CoEdition\Domain\Room;

interface StoreRoom
{
    public function __invoke(Room $room): void;
}
