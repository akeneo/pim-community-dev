<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Security;

use Akeneo\Connectivity\Connection\Application\Apps\ConnectedPimUserProviderInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConnectedPimUserProvider implements ConnectedPimUserProviderInterface
{
    public function __construct(private TokenStorageInterface $tokenStorage)
    {
    }

    public function getCurrentUserId(): int
    {
        /** @var UserInterface|null */
        $user = $this->tokenStorage->getToken()?->getUser();
        if (!$user instanceof UserInterface) {
            throw new \LogicException();
        }

        return $user->getId();
    }
}
