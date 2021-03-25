<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTestEnterprise\Pim\Automation\Integration;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\IntegrationTestsBundle\Configuration\CatalogInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\SecurityBundle\Model\AclPermission;
use Oro\Bundle\SecurityBundle\Model\AclPrivilege;
use Oro\Bundle\SecurityBundle\Model\AclPrivilegeIdentity;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * This class is used for running integration tests testing the web controllers.
 *
 * @author    Evrard Caron <evrard.caron@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
abstract class ControllerIntegrationTestCase extends WebTestCase
{
    /** @var KernelBrowser */
    protected $client;

    /** @var CatalogInterface */
    protected $catalog;

    abstract protected function getConfiguration(): Configuration;

    protected function setUp(): void
    {
        $this->client = static::createClient(['environment' => 'test', 'debug' => false]);
        $this->client->disableReboot();

        $this->catalog = $this->get('akeneo_integration_tests.catalogs');
        if (null !== $this->getConfiguration()) {
            $fixturesLoader = $this->get('akeneo_integration_tests.loader.fixtures_loader');
            $fixturesLoader->load($this->getConfiguration());
        }

        // authentication should be done after loading the database as the user is created with first activated locale as default locale
        $authenticator = $this->get('akeneo_integration_tests.security.system_user_authenticator');
        $authenticator->createSystemUser();

        $this->get('pim_connector.doctrine.cache_clearer')->clear();
    }

    protected function get(string $service)
    {
        return self::$container->get($service);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        $connectionCloser = $this->get('akeneo_integration_tests.doctrine.connection.connection_closer');
        $connectionCloser->closeConnections();

        $this->ensureKernelShutdown();
    }

    protected function enableAcl(string $aclId) : void
    {
        $aclManager = $this->get('oro_security.acl.manager');
        $roles = $this->get('pim_user.repository.role')->findAll();

        foreach ($roles as $role) {
            $privilege = new AclPrivilege();
            $identity = new AclPrivilegeIdentity($aclId);
            $privilege
                ->setIdentity($identity)
                ->addPermission(new AclPermission('EXECUTE', 1));

            $aclManager->getPrivilegeRepository()
                ->savePrivileges($aclManager->getSid($role), new ArrayCollection([$privilege]));
        }

        $aclManager->flush();
        $aclManager->clearCache();
    }
}
