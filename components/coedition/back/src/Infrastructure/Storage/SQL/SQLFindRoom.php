<?php

namespace Akeneo\CoEdition\Infrastructure\Storage\SQL;

use Akeneo\CoEdition\Application\Builder\EditorBuilder;
use Akeneo\CoEdition\Application\Builder\RoomBuilder;
use Akeneo\CoEdition\Application\Storage\FindRoom;
use Akeneo\CoEdition\Domain\Room;
use Akeneo\CoEdition\Domain\ValueObject\EditorToken;
use Akeneo\CoEdition\Domain\ValueObject\RoomUuid;
use Doctrine\DBAL\Connection;

class SQLFindRoom implements FindRoom
{
    public function __construct(
        private readonly Connection $dbalConnection
    )
    {
    }

    public function __invoke(RoomUuid $roomUuid): ?Room
    {
        $query = <<<SQL
SELECT
    BIN_TO_UUID(uuid) AS uuid,
    editors
FROM akeneo_coedition_room
WHERE uuid = :uuid
SQL;

        $data = $this->dbalConnection->fetchAssociative($query, [
            'uuid' => $roomUuid->toBytes(),
        ]);
        if ($data === false) {
            return null;
        }

        $editorsData =  json_decode($data['editors'], true, 512, JSON_THROW_ON_ERROR);

        $builder = (new RoomBuilder())
            ->withUuid(RoomUuid::fromString($data['uuid']));

        foreach ($editorsData as $editorData) {
            $editor = (new EditorBuilder())
                ->withToken(EditorToken::fromString($editorData['token']))
                ->withName($editorData['name'])
                ->withAvatar($editorData['avatar'])
                ->build();
            $builder->withEditor($editor);
        }

        return $builder->build();
    }
}
