<?php

declare(strict_types=1);

namespace Akeneo\Platform\CommunicationChannel\Application\Announcement\Query;

use Akeneo\Platform\CommunicationChannel\Domain\Announcement\Query\FindNewAnnouncementIdsInterface;
use Akeneo\Platform\CommunicationChannel\Domain\Announcement\Query\FindViewedAnnouncementIdsInterface;

/**
 * @author Christophe Chausseray <chaauseray.christophe@gmail.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class HasNewAnnouncementsHandler
{
    /** @var FindNewAnnouncementIdsInterface */
    private $findNewAnnouncementIds;

    /** @var FindViewedAnnouncementIdsInterface */
    private $findViewedAnnouncementIds;

    public function __construct(
        FindNewAnnouncementIdsInterface $findNewAnnouncementIds,
        FindViewedAnnouncementIdsInterface $findViewedAnnouncementIds
    ) {
        $this->findNewAnnouncementIds = $findNewAnnouncementIds;
        $this->findViewedAnnouncementIds = $findViewedAnnouncementIds;
    }

    public function execute(HasNewAnnouncementsQuery $query): bool
    {
        $newAnnouncementIds = $this->findNewAnnouncementIds->find($query->edition(), $query->version());
        $viewAnnouncementIds = $this->findViewedAnnouncementIds->byUserId($query->userId());

        return count(array_diff($newAnnouncementIds, $viewAnnouncementIds)) !== 0;
    }
}
