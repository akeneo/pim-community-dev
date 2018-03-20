<?php

namespace Pim\Component\User\tests\integration\Updater;

use Akeneo\Test\Integration\TestCase;

class UserUpdaterIntegration extends TestCase
{
    public function testSuccessfullyToCreateAUser()
    {
        $data = [
            'username' => 'julia',
            'first_name' => 'Julia',
            'last_name' => 'Stark',
            'email' => 'julia@example.net',
            'password' => 'julia',
            'phone' => '+33655443322',
            'roles' => ['ROLE_CATALOG_MANAGER'],
            'groups' => ['Manager'],
            'enabled' => false,
            'catalog_default_locale' => 'en_US',
            'user_default_locale' => 'en_US',
        ];

        $user = $this->get('pim_user.factory.user')->create();
        $this->get('pim_user.updater.user')->update($user, $data, []);

        $validation = $this->get('validator')->validate($user);
        $this->assertEquals(0, $validation->count());
    }

    public function testFailingToCreateAUserWhenAllFieldsAreEmpty()
    {
        $user = $this->get('pim_user.factory.user')->create();
        $this->get('pim_user.updater.user')->update($user, [], []);

        $errors = $this->get('validator')->validate($user);
        $this->assertEquals(6, $errors->count());

        $result = [];
        foreach ($errors as $error) {
            $result[$error->getPropertyPath()] = $error->getMessage();
        }

        $expected = [
            'username' => 'This value should not be blank.',
            'email' => 'This value should not be blank.',
            'firstName' => 'This value should not be blank.',
            'lastName' => 'This value should not be blank.',
            'uiLocale' => 'This value should not be blank.',
            'catalogLocale' => 'This value should not be blank.'
        ];

        $this->assertEquals($expected, $result);
    }

    public function testConstraintsOnCatalogLocale()
    {
        $data = [
            'username' => 'julia',
            'first_name' => 'Julia',
            'last_name' => 'Stark',
            'email' => 'julia@example.net',
            'password' => 'julia',
            'catalog_default_locale' => 'zh_CN',
            'user_default_locale' => 'fr_FR',
        ];
        $user = $this->get('pim_user.factory.user')->create();
        $this->get('pim_user.updater.user')->update($user, $data, []);

        $errors = $this->get('validator')->validate($user);
        $this->assertEquals(1, $errors->count());
        $this->assertEquals('catalogLocale', $errors->get(0)->getPropertyPath());
        $this->assertEquals('The locale "zh_CN" exists but has to be activated.', $errors->get(0)->getMessage());
    }

    public function testConstraintsOnPhone()
    {
        $data = [
            'username' => 'julia',
            'first_name' => 'Julia',
            'last_name' => 'Stark',
            'email' => 'julia@example.net',
            'password' => 'julia',
            'catalog_default_locale' => 'en_US',
            'user_default_locale' => 'fr_FR',
            'phone' => '0655443346'
        ];

        $user = $this->get('pim_user.factory.user')->create();
        $this->get('pim_user.updater.user')->update($user, $data, []);

        $errors = $this->get('validator')->validate($user);
        $this->assertEquals(1, $errors->count());
        $this->assertEquals('phone', $errors->get(0)->getPropertyPath());
        $this->assertEquals('The phone has to respect the international format (eg: +33755667788).', $errors->get(0)->getMessage());


        $data['phone'] = '+0655443346s';
        $this->get('pim_user.updater.user')->update($user, $data, []);

        $errors = $this->get('validator')->validate($user);
        $this->assertEquals(1, $errors->count());
        $this->assertEquals('phone', $errors->get(0)->getPropertyPath());
        $this->assertEquals('The phone has to respect the international format (eg: +33755667788).', $errors->get(0)->getMessage());
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
