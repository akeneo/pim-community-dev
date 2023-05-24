<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Channel\Integration\Channel;

use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\Pim\Enrichment\Product\Helper\FeatureHelper;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PHPUnit\Framework\Assert;

class ChannelTestCase extends TestCase
{
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function loadChannelFunctionalFixtures(): void
    {
        $this->createOrUpdateChannel(
            'mobile_app',
            [
                'locales' => [
                    'en_US',
                    'fr_FR',
                    'de_DE',
                ],
            ]
        );

        $this->createUser('mary', ['ROLE_USER'], ['Redactor']);

        if (FeatureHelper::isPermissionFeatureAvailable()) {
            $this->get('Akeneo\Pim\Permission\Bundle\Saver\UserGroupLocalePermissionsSaver')->save('All', [
                'edit' => ['all' => false, 'identifiers' => []],
                'view' => ['all' => false, 'identifiers' => []],
            ]);
            $this->get('Akeneo\Pim\Permission\Bundle\Saver\UserGroupLocalePermissionsSaver')->save('Redactor', [
                'edit' => ['all' => false, 'identifiers' => ['en_US', 'fr_FR']],
                'view' => ['all' => false, 'identifiers' => ['en_US', 'fr_FR', 'de_DE']],
            ]);
        }
    }

    /**
     * @param string[] $stringRoles
     * @param string[] $groupNames
     */
    protected function createUser(string $username, array $stringRoles, array $groupNames): UserInterface
    {
        $user = $this->get('pim_user.factory.user')->create();
        $user->setUsername($username);
        $user->setFirstName($username);
        $user->setLastName($username);
        $user->setPassword('password');
        $user->setEmail($username.'@example.com');

        $groups = $this->get('pim_user.repository.group')->findAll();
        foreach ($groups as $group) {
            if (\in_array($group->getName(), $groupNames)) {
                $user->addGroup($group);
            }
        }

        $roles = $this->get('pim_user.repository.role')->findAll();
        foreach ($roles as $role) {
            if (\in_array($role->getRole(), $stringRoles)) {
                $user->addRole($role);
            }
        }

        $violations = $this->get('validator')->validate($user);
        Assert::assertSame(0, $violations->count(), (string) $violations);
        $this->get('pim_user.saver.user')->save($user);

        return $user;
    }

    /**
     * @param ChannelInterface[] $channels
     */
    protected function createLocale(string $localeCode, array $channels = []): LocaleInterface
    {
        $locale = $this->get('pim_catalog.factory.locale')->create();
        $locale->setCode($localeCode);
        $locale->setChannels($channels);

        $violations = $this->get('validator')->validate($locale);
        Assert::assertSame(0, $violations->count(), (string) $violations);
        $this->get('pim_catalog.saver.locale')->save($locale);

        return $locale;
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function createOrUpdateChannel(string $code, array $data = []): ChannelInterface
    {
        $defaultData = [
            'code' => $code,
            'locales' => ['en_US'],
            'currencies' => ['USD'],
            'category_tree' => 'master',
        ];
        $data = array_merge($defaultData, $data);

        $channel = $this->get('pim_catalog.repository.channel')->findOneByIdentifier($code);
        if (null === $channel) {
            $channel = $this->get('pim_catalog.factory.channel')->create();
        }

        $this->get('pim_catalog.updater.channel')->update($channel, $data);
        $violations = $this->get('validator')->validate($channel);
        Assert::assertSame(0, $violations->count(), (string) $violations);
        $this->get('pim_catalog.saver.channel')->save($channel);

        return $channel;
    }
}
