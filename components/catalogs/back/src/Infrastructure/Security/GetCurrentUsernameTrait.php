<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Security;

use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
trait GetCurrentUsernameTrait
{
    private TokenStorageInterface $tokenStorage;

    private function getCurrentUsername(): string
    {
        $user = $this->tokenStorage->getToken()?->getUser();

        if (null === $user) {
            throw new \LogicException('User should not be null');
        }

        if (!$user instanceof UserInterface) {
            throw new \LogicException(\sprintf('User should be an instance of %s', UserInterface::class));
        }

        return $user->getUserIdentifier();
    }
}
