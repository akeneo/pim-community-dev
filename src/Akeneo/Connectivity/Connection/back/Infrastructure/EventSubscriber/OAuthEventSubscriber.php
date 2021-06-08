<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\EventSubscriber;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Write\Connection;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Repository\ConnectionRepository;
use Akeneo\Connectivity\Connection\Infrastructure\Service\CreateAppUserWithPermissions;
use Akeneo\Tool\Bundle\ApiBundle\Entity\Client;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\UserRepository;
use Akeneo\UserManagement\Component\Model\User;
use FOS\OAuthServerBundle\Event\OAuthEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class OAuthEventSubscriber implements EventSubscriberInterface
{
    private ConnectionRepository $connectionRepository;
    private UserRepository $userRepository;
    private CreateAppUserWithPermissions $createAppUserWithPermissions;
    private RequestStack $requestStack;

    public function __construct(
        ConnectionRepository $connectionRepository,
        UserRepository $userRepository,
        CreateAppUserWithPermissions $createAppUserWithPermissions,
        RequestStack $requestStack
    ) {
        $this->connectionRepository = $connectionRepository;
        $this->userRepository = $userRepository;
        $this->createAppUserWithPermissions = $createAppUserWithPermissions;
        $this->requestStack = $requestStack;
    }

    public static function getSubscribedEvents()
    {
        return [OAuthEvent::POST_AUTHORIZATION_PROCESS => 'saveConnection'];
    }

    public function saveConnection(OAuthEvent $event)
    {
        $client = $event->getClient();

        if (!$client instanceof Client) {
            return;
        }

        if (null === $request = $this->requestStack->getCurrentRequest()) {
            throw new \LogicException('Current request not found.');
        }

        $scopes = [];
        if (null !== $form = $request->get('fos_oauth_server_authorize_form')) {
            // get scopes for OAuth Apps
            $scopes = explode(' ', $form['scope']);
        }
        $user = $this->createAppUserWithPermissions->handle($client, $scopes);

        $connectionCode = strtr($client->getLabel(), '<>&" -', '______');
        $connection = $this->connectionRepository->findOneByCode($connectionCode);

        if(null !== $connection) {
            throw new \LogicException('Extension already exist.');
        }

        // TODO receive FlowType from the App
        $connection = new Connection(
            $connectionCode,
            $connectionCode,
            FlowType::OTHER,
            $client->getId(),
            $user->getId()
        );

        $this->connectionRepository->create($connection);
    }
}
