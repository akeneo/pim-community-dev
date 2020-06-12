<?php

declare(strict_types=1);

namespace Akeneo\Platform\CommunicationChannel\Infrastructure\Delivery\InternalApi\Announcement;

use Akeneo\Platform\CommunicationChannel\Application\Announcement\Query\ListAnnouncementsHandler;
use Akeneo\Platform\CommunicationChannel\Domain\Announcement\Model\Read\AnnouncementItem;
use Akeneo\Platform\CommunicationChannel\Domain\Announcement\Query\FindAnnouncementItemsInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @author Christophe Chausseray <chaauseray.christophe@gmail.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ListAction
{
    /** @var ListAnnouncementsHandler */
    private $listAnnouncementsHandler;

    public function __construct(ListAnnouncementsHandler $listAnnouncementsHandler)
    {
        $this->listAnnouncementsHandler = $listAnnouncementsHandler;
    }

    public function __invoke()
    {
        $announcementItems = $this->listAnnouncementsHandler->execute();

        $normalizedAnnouncementItems = $this->normalizeAnnouncementItems($announcementItems);

        return new JsonResponse([
            'items' => $normalizedAnnouncementItems
        ]);
    }

    private function normalizeAnnouncementItems(array $announcementItems): array
    {
        return array_map(function (AnnouncementItem $item) {
            return $item->toArray();
        }, $announcementItems);
    }
}
