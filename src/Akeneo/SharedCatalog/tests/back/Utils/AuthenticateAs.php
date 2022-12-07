<?php

namespace Akeneo\SharedCatalog\tests\back\Utils;

use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Akeneo\UserManagement\Component\Model\User;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Webmozart\Assert\Assert;

trait AuthenticateAs
{
    public function authenticateAs(string $username): User
    {
        Assert::isInstanceOfAny($this, [TestCase::class, ApiTestCase::class]);

        /** @var User $user */
        $user = $this->get('pim_user.provider.user')->loadUserByUsername($username);
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $this->get('security.token_storage')->setToken($token);

        return $user;
    }
}
