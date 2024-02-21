<?php


namespace Akeneo\UserManagement\Bundle\Model;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LockedAccountException extends AuthenticationException
{
    /** @var ?array */
    protected $serialized = null;

    public function __construct(int $lockduration)
    {
        parent::__construct("Your account is locked for {$lockduration} minutes after too many authentication attempts.");

        $this->serialized = null;
    }
    public function getMessageKey(): string
    {
        return $this->getMessage();
    }
}
