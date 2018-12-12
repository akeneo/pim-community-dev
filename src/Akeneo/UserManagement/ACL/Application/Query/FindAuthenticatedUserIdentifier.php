<?php

declare(strict_types=1);

namespace Akeneo\UserManagement\ACL\Application\Query;

use Akeneo\UserManagement\ACL\Domain\UserIdentifier;
use Akeneo\UserManagement\Bundle\Context\UserContext;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FindAuthenticatedUserIdentifier
{
    /** @var UserContext */
    private $userContext;

    public function __construct(UserContext $userContext)
    {
        $this->userContext = $userContext;
    }

    public function __invoke(): ?UserIdentifier
    {
        $user = $this->userContext->getUser();

        return null !== $user ? UserIdentifier::fromString($user->getUsername()) : null;
    }
}
