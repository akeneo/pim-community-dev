<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\EndToEnd;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\IntegrationTestsBundle\Configuration\CatalogInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Model\AclPermission;
use Oro\Bundle\SecurityBundle\Model\AclPrivilege;
use Oro\Bundle\SecurityBundle\Model\AclPrivilegeIdentity;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 */
abstract class ControllerEndToEndTestCase extends WebTestCase
{
    protected KernelBrowser $client;

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
        return self::getContainer()->get($service);
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

    protected function enableAcl(string $aclId, $role = 'ROLE_ADMINISTRATOR') : void
    {
        $aclManager = $this->get('oro_security.acl.manager');
        $role = $this->get('pim_user.repository.role')->findByIdentifier($role);
        $privilege = new AclPrivilege();
        $identity = new AclPrivilegeIdentity($aclId);
        $privilege
            ->setIdentity($identity)
            ->addPermission(new AclPermission('EXECUTE', AccessLevel::BASIC_LEVEL));
        $aclManager->getPrivilegeRepository()->savePrivileges(
            $aclManager->getSid($role),
            new ArrayCollection([$privilege])
        );
        $aclManager->flush();
        $aclManager->clearCache();
    }

    protected function disableAcl(string $aclPrivilegeIdentityId, $role = 'ROLE_ADMINISTRATOR') : void
    {
        $aclManager = $this->get('oro_security.acl.manager');
        $role = $this->get('pim_user.repository.role')->findOneByIdentifier($role);
        $privilege = new AclPrivilege();
        $identity = new AclPrivilegeIdentity($aclPrivilegeIdentityId);
        $privilege
            ->setIdentity($identity)
            ->addPermission(new AclPermission('EXECUTE', AccessLevel::NONE_LEVEL));
        $aclManager->getPrivilegeRepository()->savePrivileges(
            $aclManager->getSid($role),
            new ArrayCollection([$privilege])
        );
        $aclManager->flush();
        $aclManager->clearCache();
    }
}
