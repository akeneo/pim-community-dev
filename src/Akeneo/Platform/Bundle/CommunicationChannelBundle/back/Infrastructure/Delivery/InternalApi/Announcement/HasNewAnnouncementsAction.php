<?php

declare(strict_types=1);

namespace Akeneo\Platform\CommunicationChannel\Infrastructure\Delivery\InternalApi\Announcement;

use Akeneo\Platform\CommunicationChannel\Application\Announcement\Query\HasNewAnnouncementsHandler;
use Akeneo\Platform\CommunicationChannel\Application\Announcement\Query\HasNewAnnouncementsQuery;
use Akeneo\Platform\VersionProviderInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author Christophe Chausseray <chausseray.christophe@gmail.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class HasNewAnnouncementsAction
{
    /** @var VersionProviderInterface */
    private $versionProvider;

    /** @var UserContext */
    private $userContext;

    /** @var HasNewAnnouncementsHandler */
    private $hasNewAnnouncementsHandler;

    public function __construct(
        VersionProviderInterface $versionProviderInterface,
        UserContext $userContext,
        HasNewAnnouncementsHandler $hasNewAnnouncementsHandler
    ) {
        $this->versionProvider = $versionProviderInterface;
        $this->userContext = $userContext;
        $this->hasNewAnnouncementsHandler = $hasNewAnnouncementsHandler;
    }

    public function __invoke()
    {
        if (null === $user = $this->userContext->getUser()) {
            throw new NotFoundHttpException('Current user not found');
        }

        $query = new HasNewAnnouncementsQuery(
            $this->versionProvider->getEdition(),
            $this->versionProvider->getMinorVersion(),
            $user->getId()
        );
        $hasNewAnnouncements = $this->hasNewAnnouncementsHandler->execute($query);

        return new JsonResponse([
            'status' => $hasNewAnnouncements
        ]);
    }
}
