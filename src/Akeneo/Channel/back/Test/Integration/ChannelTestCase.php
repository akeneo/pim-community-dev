<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Channel\Test\Integration;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
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
        $this->createChannel(
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

        if (FeatureHelper::isPermissionFeatureActivated()) {
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

    protected function createUser(string $username, array $stringRoles, array $groupNames): UserInterface
    {
        $user = $this->get('pim_user.factory.user')->create();
        $user->setUsername($username);
        $user->setPassword('password');
        $user->setEmail($username . '@example.com');

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

        $this->get('validator')->validate($user);
        $this->get('pim_user.saver.user')->save($user);

        return $user;
    }

    /**
     * @param string $localeCode
     * @param ChannelInterface[] $channels
     *
     * @return void
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

    protected function createChannel(string $code, array $data = []): ChannelInterface
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
        $errors = $this->get('validator')->validate($channel);

        $errorMessage = '';
        foreach ($errors as $error) {
            $errorMessage .= PHP_EOL.$error->getMessage();
        }

        Assert::assertCount(0, $errors, 'Invalid channel: ' . $errorMessage);

        $this->get('pim_catalog.saver.channel')->save($channel);

        return $channel;
    }
}
