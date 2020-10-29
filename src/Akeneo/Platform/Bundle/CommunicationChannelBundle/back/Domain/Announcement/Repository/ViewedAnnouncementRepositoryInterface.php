<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\CommunicationChannelBundle\back\Domain\Announcement\Repository;

use Akeneo\Platform\Bundle\CommunicationChannelBundle\back\Domain\Announcement\Model\Write\ViewedAnnouncement;

interface ViewedAnnouncementRepositoryInterface
{
    /**
     * @param ViewedAnnouncement[] $viewedAnnouncements
     */
    public function create(array $viewedAnnouncements): void;
}
