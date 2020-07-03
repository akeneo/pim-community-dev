<?php

declare(strict_types=1);

namespace Akeneo\Platform\CommunicationChannel\Infrastructure\Delivery\InternalApi\Announcement;

use Akeneo\Platform\CommunicationChannel\Application\Announcement\Query\ListAnnouncementsHandler;
use Akeneo\Platform\CommunicationChannel\Application\Announcement\Query\ListAnnouncementsQuery;
use Akeneo\Platform\CommunicationChannel\Domain\Announcement\Model\Read\AnnouncementItem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

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

    public function __invoke(Request $request)
    {
        if (!$request->query->has('limit')) {
            throw new UnprocessableEntityHttpException('You should give a "limit" key.');
        }

        $query = new ListAnnouncementsQuery(
            $request->query->get('search_after'),
            (int) $request->query->get('limit')
        );
        $announcementItems = $this->listAnnouncementsHandler->execute($query);

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
