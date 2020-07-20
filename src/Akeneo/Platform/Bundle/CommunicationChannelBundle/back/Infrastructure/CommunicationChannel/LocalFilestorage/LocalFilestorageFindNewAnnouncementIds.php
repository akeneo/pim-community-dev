<?php

declare(strict_types=1);

namespace Akeneo\Platform\CommunicationChannel\Infrastructure\CommunicationChannel\LocalFilestorage;

use Akeneo\Platform\CommunicationChannel\Domain\Announcement\Query\FindNewAnnouncementIdsInterface;

/**
 * @author Christophe Chausseray <chausseray.christophe@gmail.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class LocalFilestorageFindNewAnnouncementIds implements FindNewAnnouncementIdsInterface
{
    private const FILENAME = 'serenity-updates.json';

    /** @var string */
    private $externalJson;

    public function __construct()
    {
        $this->externalJson = file_get_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . self::FILENAME);
    }

    public function find(string $pimEdition, string $pimVersion): array
    {
        $content = json_decode($this->externalJson, true);

        $currentDate = new \DateTimeImmutable();

        $newAnnouncementIds = [];
        foreach ($content['data'] as $announcement) {
            $dateInterval = new \DateInterval(sprintf('P%sD', $announcement['notificationDuration']));
            $startDate = new \DateTimeImmutable($announcement['startDate']);
            $endDate = $startDate->add($dateInterval);
            if ($currentDate > $startDate && $currentDate < $endDate) {
                $newAnnouncementIds[] = $announcement['id'];
            }
        }

        return $newAnnouncementIds;
    }
}
