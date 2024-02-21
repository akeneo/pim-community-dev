<?php

declare(strict_types=1);

namespace Akeneo\Platform\CommunicationChannel\Infrastructure\Delivery\InternalApi\Announcement;

use Akeneo\Platform\CommunicationChannel\Application\Announcement\Command\AddViewedAnnouncementsByUserCommand;
use Akeneo\Platform\CommunicationChannel\Application\Announcement\Command\AddViewedAnnouncementsByUserHandler;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * @author Christophe Chausseray <chausseray.christophe@gmail.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AddViewedAnnouncementsAction
{
    /** @var UserContext */
    private $userContext;

    /** @var AddViewedAnnouncementsByUserHandler */
    private $addViewedAnnouncementsByUserHandler;

    public function __construct(UserContext $userContext, AddViewedAnnouncementsByUserHandler $addViewedAnnouncementsByUserHandler)
    {
        $this->userContext = $userContext;
        $this->addViewedAnnouncementsByUserHandler = $addViewedAnnouncementsByUserHandler;
    }

    public function __invoke(Request $request)
    {
        if (!$request->request->has('viewed_announcement_ids')) {
            throw new UnprocessableEntityHttpException('You should give a "viewed_announcements_ids" key.');
        }

        if (null === $user = $this->userContext->getUser()) {
            throw new NotFoundHttpException('Current user not found');
        }

        $command = new AddViewedAnnouncementsByUserCommand(
            (array) $request->request->get('viewed_announcement_ids'),
            $user->getId()
        );
        $this->addViewedAnnouncementsByUserHandler->execute($command);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
