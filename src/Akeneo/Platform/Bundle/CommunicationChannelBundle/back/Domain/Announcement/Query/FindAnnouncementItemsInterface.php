<?php

declare(strict_types=1);

namespace Akeneo\Platform\CommunicationChannel\Domain\Announcement\Query;

interface FindAnnouncementItemsInterface
{
    /**
     * @return AnnouncementItem[]
     */
    public function byUserAndPimVersion(): array;
}
