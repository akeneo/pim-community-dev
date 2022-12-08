<?php

namespace Akeneo\CoEdition\Infrastructure\Controller;

use Akeneo\CoEdition\Application\EnterRoom;
use Akeneo\CoEdition\Application\Exception\EditorNotFoundException;
use Akeneo\CoEdition\Application\Storage\FindEditor;
use Akeneo\CoEdition\Domain\ValueObject\RoomUuid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class EnterRoomAction
{
    public function __construct(
        private FindEditor $findEditor,
        private EnterRoom  $enterRoom
    )
    {
    }

    public function __invoke(Request $request, string $roomId): JsonResponse
    {
        $editorId = $request->request->get('editor');
        $editor = ($this->findEditor)($editorId);

        if ($editor === null) {
            throw new EditorNotFoundException(sprintf('Editor with identifier "%s" is not found', $editorId));
        }

        $roomUuid = RoomUuid::fromString($roomId);

        ($this->enterRoom)($roomUuid, $editor);

        return new JsonResponse();
    }

}
