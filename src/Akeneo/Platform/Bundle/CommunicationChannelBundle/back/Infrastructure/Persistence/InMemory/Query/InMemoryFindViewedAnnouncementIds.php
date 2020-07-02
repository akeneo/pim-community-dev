<?php

declare(strict_types=1);

namespace Akeneo\Platform\CommunicationChannel\Infrastructure\Persistence\InMemory\Query;

use Akeneo\Platform\CommunicationChannel\Domain\Announcement\Query\FindViewedAnnouncementIdsInterface;
use Akeneo\Platform\CommunicationChannel\Infrastructure\Persistence\InMemory\Repository\InMemoryViewedAnnouncementRepository;

/**
 * @author    Christophe Chausseray <chausseray.christophe@gmail.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryFindViewedAnnouncementIds implements FindViewedAnnouncementIdsInterface
{
    /** @var InMemoryViewedAnnouncementRepository */
    private $viewedAnnouncementRepository;

    public function __construct(InMemoryViewedAnnouncementRepository $viewedAnnouncementRepository)
    {
        $this->viewedAnnouncementRepository = $viewedAnnouncementRepository;
    }

    public function byUserId(int $userId): array
    {
        $viewedAnnouncements = array_filter($this->viewedAnnouncementRepository->dataRows, function ($row) use ($userId) {
            return $row['user_id'] === $userId;
        });

        return array_map(function (array $viewedAnnouncement) {
            return $viewedAnnouncement['announcement_id'];
        }, $viewedAnnouncements);
    }
}
