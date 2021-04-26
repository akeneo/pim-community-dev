<?php


namespace Akeneo\UserManagement\Bundle\Provider;


use Akeneo\UserManagement\Bundle\Manager\UserManager;
use Akeneo\UserManagement\Component\Model\User;
use Symfony\Component\Security\Core\Authentication\Provider\DaoAuthenticationProvider;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class CustomDaoAuthenticationProvider extends DaoAuthenticationProvider
{
    /** @var UserManager */
private $userManager;

    public function __construct(UserProviderInterface $userProvider, UserCheckerInterface $userChecker, string $providerKey, EncoderFactoryInterface $encoderFactory, UserManager $userManager, bool $hideUserNotFoundExceptions = true)
    {
        parent::__construct($userProvider, $userChecker,$providerKey, $encoderFactory,$hideUserNotFoundExceptions);
        $this->userManager = $userManager;
    }

    protected function checkAuthentication(UserInterface $user, UsernamePasswordToken $token)
    {
        //Check user can log in
        if ($user instanceof User) {

        }
    }
    }