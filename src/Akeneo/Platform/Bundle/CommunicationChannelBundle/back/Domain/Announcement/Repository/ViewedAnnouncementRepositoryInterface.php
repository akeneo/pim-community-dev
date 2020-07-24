<?php

declare(strict_types=1);

namespace Akeneo\Platform\CommunicationChannel\Domain\Announcement\Repository;

use Akeneo\Platform\CommunicationChannel\Domain\Announcement\Model\Write\ViewedAnnouncement;

interface ViewedAnnouncementRepositoryInterface
{
    /**
     * @param ViewedAnnouncement[] $viewedAnnouncements
     */
    public function create(array $viewedAnnouncements): void;
}
