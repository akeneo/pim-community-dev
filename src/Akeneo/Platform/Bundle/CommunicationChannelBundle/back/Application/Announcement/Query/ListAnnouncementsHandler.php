<?php

declare(strict_types=1);

namespace Akeneo\Platform\CommunicationChannel\Application\Announcement\Query;

use Akeneo\Platform\CommunicationChannel\Domain\Announcement\Model\Read\AnnouncementItem;
use Akeneo\Platform\CommunicationChannel\Domain\Announcement\Query\FindAnnouncementItemsInterface;
use Akeneo\Platform\CommunicationChannel\Domain\Announcement\Query\FindViewedAnnouncementIdsInterface;

/**
 * @author Christophe Chausseray <chausseray.christophe@gmail.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class ListAnnouncementsHandler
{
    /** @var FindAnnouncementItemsInterface */
    private $findAnnouncementItems;

    /** @var FindViewedAnnouncementIdsInterface */
    private $findViewedAnnouncementIds;

    public function __construct(
        FindAnnouncementItemsInterface $findAnnouncementItems,
        FindViewedAnnouncementIdsInterface $findViewedAnnouncementIds
    ) {
        $this->findAnnouncementItems = $findAnnouncementItems;
        $this->findViewedAnnouncementIds = $findViewedAnnouncementIds;
    }

    /**
     * @return AnnouncementItem[]
     */
    public function execute(ListAnnouncementsQuery $query): array
    {
        $announcementItems = $this->findAnnouncementItems->byPimVersion($query->edition(), $query->version(), $query->searchAfter());
        $viewedAnnouncementIds = $this->findViewedAnnouncementIds->byUserId($query->userId());

        $announcementItemsWithNew = [];
        foreach ($announcementItems as $announcementItem) {
            $announcementItemsWithNew[] =  $announcementItem->shouldBeNotified($viewedAnnouncementIds) ? $announcementItem->toNotify() : $announcementItem;
        }

        return $announcementItemsWithNew;
    }
}
