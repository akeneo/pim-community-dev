<?php

namespace Akeneo\CoEdition\Application\Builder;

use Akeneo\CoEdition\Domain\Editor;
use Akeneo\CoEdition\Domain\Room;
use Akeneo\CoEdition\Domain\ValueObject\RoomUuid;
use Webmozart\Assert\Assert;

class RoomBuilder
{
    private ?RoomUuid $uuid;

    /** @var Editor[] */
    private array $editors;

    public function __construct()
    {
        $this->uuid = null;
        $this->editors = [];
    }

    public function withUuid(RoomUuid $roomUuid): self
    {
        $this->uuid = $roomUuid;
        return $this;
    }

    public function withEditor(Editor $editor): self
    {
        $this->editors[] = $editor;
        return $this;
    }

    public function withEditors(array $editors): self
    {
        Assert::allIsInstanceOf($editors, Editor::class);

        foreach ($editors as $editor) {
            $this->withEditor($editor);
        }
        return $this;
    }

    public function build(): Room
    {
        Assert::isInstanceOf($this->uuid, RoomUuid::class, 'The room token uuid must be provided');
        
        return new Room(
            $this->uuid,
            $this->editors,
        );
    }

}
