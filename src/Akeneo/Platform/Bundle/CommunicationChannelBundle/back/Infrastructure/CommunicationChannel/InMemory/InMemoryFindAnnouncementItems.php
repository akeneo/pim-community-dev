<?php

declare(strict_types=1);

namespace Akeneo\Platform\CommunicationChannel\Infrastructure\CommunicationChannel\InMemory;

use Akeneo\Platform\CommunicationChannel\Domain\Announcement\Model\Read\AnnouncementItem;
use Akeneo\Platform\CommunicationChannel\Domain\Announcement\Query\FindAnnouncementItemsInterface;

/**
 * @author Christophe Chausseray <chaauseray.christophe@gmail.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class InMemoryFindAnnouncementItems implements FindAnnouncementItemsInterface
{
    private const FILENAME = 'serenity-updates.json';

    /** @var string */
    private $externalJson;

    public function __construct()
    {
        $this->externalJson = file_get_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . self::FILENAME);
    }

    public function byPimVersion(string $pimEdition, string $pimVersion): array
    {
        $content = json_decode($this->externalJson, true);

        return array_map(function ($announcement) {
            return $this->getAnnouncementItem($announcement);
        }, $content['data']);
    }

    private function getAnnouncementItem(array $announcement): AnnouncementItem
    {
        return new AnnouncementItem(
            $announcement['title'],
            $announcement['description'],
            $announcement['img'] ?? null,
            $announcement['altImg'] ?? null,
            $announcement['link'],
            new \DateTimeImmutable($announcement['startDate']),
            $announcement['notificationDuration'],
            $announcement['tags'],
            $announcement['editions']
        );
    }
}
