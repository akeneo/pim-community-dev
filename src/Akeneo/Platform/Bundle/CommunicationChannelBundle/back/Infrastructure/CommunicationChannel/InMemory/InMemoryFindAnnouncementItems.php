<?php

declare(strict_types=1);

namespace Akeneo\Platform\CommunicationChannel\Infrastructure\CommunicationChannel\InMemory;

use Akeneo\Platform\CommunicationChannel\Domain\Announcement\Model\Read\AnnouncementItem;
use Akeneo\Platform\CommunicationChannel\Domain\Announcement\Query\FindAnnouncementItemsInterface;
use Akeneo\Platform\CommunicationChannel\Infrastructure\Filesystem\Encoder\ImageLinkBase64Encoder;

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

    /** @var string */
    private $rootDirectory;

    public function __construct(string $rootDirectory)
    {
        $this->externalJson = file_get_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . self::FILENAME);
        $this->rootDirectory = $rootDirectory;
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
        if (isset($announcement['img'])) {
            $imageLink = $this->formatImageLink($announcement['img']);
            $imageEncoded = ImageLinkBase64Encoder::encode($imageLink);
        } else {
            $imageEncoded = null;
        }

        return new AnnouncementItem(
            $announcement['title'],
            $announcement['description'],
            $imageEncoded,
            $announcement['altImg'] ?? null,
            $announcement['link'],
            new \DateTimeImmutable($announcement['startDate']),
            $announcement['notificationDuration'],
            $announcement['tags'],
            $announcement['editions']
        );
    }

    private function formatImageLink($image)
    {
        if (filter_var($image, FILTER_VALIDATE_URL)) {
            return $image;
        }

        return $this->rootDirectory . '/public' . $image;
    }
}
