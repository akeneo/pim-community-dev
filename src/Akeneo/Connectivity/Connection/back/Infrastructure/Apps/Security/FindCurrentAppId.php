<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Security;

use Akeneo\Connectivity\Connection\Application\Apps\Security\FindCurrentAppIdInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FindCurrentAppId implements FindCurrentAppIdInterface
{
    public function __construct(
        private TokenStorageInterface $tokenStorage,
    ) {
    }

    /**
     * @inheritdoc
     */
    public function execute(): ?string
    {
        /** @var UserInterface|null $user */
        $user = $this->tokenStorage->getToken()?->getUser();

        if (!$user instanceof UserInterface) {
            return null;
        }

        return $user->getProperty('app_id');
    }
}
