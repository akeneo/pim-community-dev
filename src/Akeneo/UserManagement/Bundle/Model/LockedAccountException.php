<?php


namespace Akeneo\UserManagement\Bundle\Model;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class LockedAccountException extends AuthenticationException
{
    public function __construct(int $lockduration)
    {
        parent::__construct("Your account has been locked for {$lockduration} seconds after too many authentication attempts.");
    }
    public function getMessageKey()
    {
        return $this->getMessage();
    }
}
