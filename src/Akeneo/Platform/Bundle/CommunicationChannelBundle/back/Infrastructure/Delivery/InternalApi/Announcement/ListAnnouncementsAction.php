<?php

declare(strict_types=1);

namespace Akeneo\Platform\CommunicationChannel\Infrastructure\Delivery\InternalApi\Announcement;

use Akeneo\Platform\CommunicationChannel\Application\Announcement\Query\ListAnnouncementsHandler;
use Akeneo\Platform\CommunicationChannel\Application\Announcement\Query\ListAnnouncementsQuery;
use Akeneo\Platform\CommunicationChannel\Domain\Announcement\Model\Read\AnnouncementItem;
use Akeneo\Platform\VersionProviderInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * @author Christophe Chausseray <chausseray.christophe@gmail.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ListAnnouncementsAction
{
    /** @var VersionProviderInterface */
    private $versionProvider;

    /** @var UserContext */
    private $userContext;

    /** @var ListAnnouncementsHandler */
    private $listAnnouncementsHandler;

    public function __construct(VersionProviderInterface $versionProviderInterface, UserContext $userContext, ListAnnouncementsHandler $listAnnouncementsHandler)
    {
        $this->versionProvider = $versionProviderInterface;
        $this->userContext = $userContext;
        $this->listAnnouncementsHandler = $listAnnouncementsHandler;
    }

    public function __invoke(Request $request)
    {
        if (null === $user = $this->userContext->getUser()) {
            throw new NotFoundHttpException('Current user not found');
        }

        $query = new ListAnnouncementsQuery(
            $this->versionProvider->getEdition(),
            $this->versionProvider->getMinorVersion(),
            $user->getId(),
            $request->query->get('search_after')
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
