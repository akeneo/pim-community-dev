<?php

declare(strict_types=1);

namespace Akeneo\Platform\CommunicationChannel\Infrastructure\CommunicationChannel\InMemory;

use Akeneo\Platform\CommunicationChannel\Domain\Announcement\Model\Read\AnnouncementItem;
use Akeneo\Platform\CommunicationChannel\Domain\Announcement\Query\FindAnnouncementItemsInterface;

/**
 * @author Christophe Chausseray <chausseray.christophe@gmail.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class InMemoryFindAnnouncementItems implements FindAnnouncementItemsInterface
{
    private const FILENAME = 'serenity-updates.json';

    /** @var string */
    private $externalJson;

    /** @var int */
    private $cptItems;

    /** @var int|null */
    private $itemToSearchAfter;

    public function __construct()
    {
        $this->externalJson = file_get_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . self::FILENAME);
        $this->cptItems = 0;
        $this->itemToSearchAfter = null;
    }

    public function byPimVersion(string $pimEdition, string $pimVersion, ?string $searchAfter, int $limit): array
    {
        $content = json_decode($this->externalJson, true);

        $paginatedItems = $this->paginateItems($content, $limit, $searchAfter);

        return array_map(function ($announcement) {
            return $this->getAnnouncementItem($announcement);
        }, array_values($paginatedItems));
    }

    private function getAnnouncementItem(array $announcement): AnnouncementItem
    {
        return new AnnouncementItem(
            $announcement['id'],
            $announcement['title'],
            $announcement['description'],
            $announcement['img'] ?? null,
            $announcement['altImg'] ?? null,
            $announcement['link'],
            new \DateTimeImmutable($announcement['startDate']),
            $announcement['notificationDuration'],
            $announcement['tags']
        );
    }

    private function paginateItems(array $content, int $limit, string $searchAfter = null): array
    {
        $paginatedItems = $content['data'];
        if (null === $searchAfter) {
            return array_slice($paginatedItems, 0, $limit);
        }

        $searchAfterIdKey = array_search($searchAfter, array_column($paginatedItems, 'id'));

        return array_slice($paginatedItems, $searchAfterIdKey + 1, $limit);
    }
}
