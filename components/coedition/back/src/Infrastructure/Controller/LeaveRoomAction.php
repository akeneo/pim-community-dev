<?php

namespace Akeneo\CoEdition\Infrastructure\Controller;

use Akeneo\CoEdition\Application\Exception\EditorNotFoundException;
use Akeneo\CoEdition\Application\LeaveRoom;
use Akeneo\CoEdition\Application\Storage\FindEditor;
use Akeneo\CoEdition\Domain\ValueObject\RoomUuid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class LeaveRoomAction
{
    public function __construct(
        private readonly FindEditor $findEditor,
        private readonly LeaveRoom $leaveRoom
    )
    {
    }

    /**
     * @throws EditorNotFoundException
     */
    public function __invoke(Request $request, string $roomId): JsonResponse
    {
        $editorId = $request->request->get('editor');
        $editor = ($this->findEditor)($editorId);

        if ($editor === null) {
            throw new EditorNotFoundException(sprintf('Editor with identifier "%s" is not found', $editorId));
        }

        ($this->leaveRoom)(RoomUuid::fromString($roomId), $editor);

        return new JsonResponse();
    }

}
