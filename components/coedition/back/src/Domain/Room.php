<?php

namespace Akeneo\CoEdition\Domain;

use Akeneo\CoEdition\Domain\ValueObject\RoomUuid;

class Room
{
    public function __construct(
        private RoomUuid $roomUuid,
        /** @param Editor[] $editors */
        private array $editors,
    )
    {
        // @todo control editors should be an array of editors
    }

    public function getRoomUuid(): RoomUuid
    {
        return $this->roomUuid;
    }

    public function getEditors(): array
    {
        return $this->editors;
    }

    public function enter(Editor $editor): void
    {
        // @todo check uniqueness
        $this->editors[] = $editor;
    }

    public function leave(Editor $editor): void
    {
        $this->editors = \array_filter($this->editors, static function (Editor $e) use ($editor) {
            return $e->getToken() !== $editor->getToken();
        });
    }
}
