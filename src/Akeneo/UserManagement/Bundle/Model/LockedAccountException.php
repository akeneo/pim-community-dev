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
//    public function __construct(UserProviderInterface $userProvider, UserCheckerInterface $userChecker, string $providerKey, EncoderFactoryInterface $encoderFactory, bool $hideUserNotFoundExceptions = true)
//    {
//        parent::__construct(UserProviderInterface $userProvider,  $userChecker,  $providerKey,  $encoderFactory,  $hideUserNotFoundExceptions);
//    }
//
//    protected function checkAuthentication(UserInterface $user, UsernamePasswordToken $token)
//    {
//        parent::checkAuthentication($user, $token);
//    }
}