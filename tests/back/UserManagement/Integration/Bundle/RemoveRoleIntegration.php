<?php

namespace AkeneoTest\UserManagement\Integration\Bundle;

use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Component\Exception\ForbiddenToRemoveRoleException;

class RemoveRoleIntegration extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->createAdminUser();
    }

    public function testUnableToRemoveARoleIfUsersWillNoLongerHaveRole()
    {
        $this->expectException(ForbiddenToRemoveRoleException::class);
        $this->expectExceptionMessage('You can not delete this role, otherwise some users will no longer have a role.');

        $adminRole = $this->get('pim_user.repository.role')->findOneByIdentifier('ROLE_ADMINISTRATOR');
        $this->get('pim_user.remover.role')->remove($adminRole);
    }

    public function testSuccessfullyToRemoveARole()
    {
        $adminRole = $this->get('pim_user.repository.role')->findOneByIdentifier('ROLE_CATALOG_MANAGER');
        $this->get('pim_user.remover.role')->remove($adminRole);
        $this->assertNull($this->get('pim_user.repository.role')->findOneByIdentifier('ROLE_CATALOG_MANAGER'));
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
