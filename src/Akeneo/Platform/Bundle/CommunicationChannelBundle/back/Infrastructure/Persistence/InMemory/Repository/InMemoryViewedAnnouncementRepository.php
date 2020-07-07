<?php

declare(strict_types=1);

namespace Akeneo\Platform\CommunicationChannel\Infrastructure\Persistence\InMemory\Repository;

use Akeneo\Platform\CommunicationChannel\Domain\Announcement\Repository\ViewedAnnouncementRepositoryInterface;

/**
 * @author    Christophe Chausseray <chausseray.christophe@gmail.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryViewedAnnouncementRepository implements ViewedAnnouncementRepositoryInterface
{
    /** @var array */
    public $dataRows = [];

    /**
     * {@inheritdoc}
     */
    public function create(array $viewedAnnouncements): void
    {
        foreach ($viewedAnnouncements as $viewedAnnouncement) {
            $this->dataRows[] = [
                'announcement_id' => $viewedAnnouncement->announcementId(),
                'user_id' => $viewedAnnouncement->userId(),
            ];
        }
    }
}
