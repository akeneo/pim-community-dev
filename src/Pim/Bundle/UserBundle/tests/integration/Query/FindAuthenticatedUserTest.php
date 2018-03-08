<?php

namespace Pim\Bundle\UserBundle\tests\integration\Query;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Pim\Bundle\UserBundle\Persistence\ORM\Query\FindAuthenticatedUser;

class FindAuthenticatedUserIntegration extends TestCase
{
    /**
     * @test
     */
    public function it_finds_user_information_for_authentication(): void
    {
        /**
         * FindAuthenticatedUser is defined as a service, we want to keep it private that why we instance it
         * instead of calling the service from the container
         */
        $query = new FindAuthenticatedUser($this->getFromTestContainer('doctrine.orm.entity_manager'));
        $user = $query('admin');

        $password = $this->getFromTestContainer('security.encoder_factory')->getEncoder($user)->encodePassword(
            'admin',
            $user->getSalt()
        );

        $this->assertSame('admin', $user->getUsername(), 'The username is wrong');
        $this->assertSame($password, $user->getPassword(), 'The username is wrong');
        $this->assertSame(['ROLE_ADMINISTRATOR'], $user->getRoles(), 'The roles are wrong');
        $this->assertSame('en_US', $user->getUiLocale(), 'The UI locale is wrong');
        $this->assertSame(true, $user->isAccountNonLocked(), 'The "accountNonLocked" is wrong');
        $this->assertSame(true, $user->isEnabled(), 'The status is wrong');
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
