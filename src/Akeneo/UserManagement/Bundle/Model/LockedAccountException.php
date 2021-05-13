<?php


namespace Akeneo\UserManagement\Bundle\Model;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
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
    public function __construct(int $lockduration)
    {
        parent::__construct("Your account is locked for {$lockduration} minutes after too many authentication attempts.");
    }
    public function getMessageKey()
    {
        return $this->getMessage();
    }
}
