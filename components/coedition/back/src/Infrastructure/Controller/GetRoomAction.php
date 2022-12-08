<?php

namespace Akeneo\CoEdition\Infrastructure\Controller;

use Akeneo\CoEdition\Application\Builder\RoomBuilder;
use Akeneo\CoEdition\Application\Storage\FindRoom;
use Akeneo\CoEdition\Domain\Editor;
use Akeneo\CoEdition\Domain\ValueObject\RoomUuid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class GetRoomAction
{
    public function __construct(
        private readonly FindRoom $findRoom,
    )
    {
    }
    public function __invoke(Request $request, string $roomId): JsonResponse
    {
        $roomUuid = RoomUuid::fromString($roomId);
        $room = ($this->findRoom)($roomUuid);

        if ($room === null) {
            $room = (new RoomBuilder())
                ->withUuid($roomUuid)
                ->build();
        }

        return new JsonResponse([
            'roomId' => $room->getRoomUuid(),
            'editors' => array_map(static function (Editor $editor) {
                return [
                  'id' => $editor->getToken(),
                  'name' => $editor->getName(),
                  'avatar' => $editor->getAvatar(),
                ];
            }, $room->getEditors()),
        ]);
    }
}
