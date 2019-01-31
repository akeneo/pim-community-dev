<?php

namespace AkeneoTest\UserManagement\Integration\Bundle;

use Akeneo\Test\Integration\TestCase;

class RemoveRoleIntegration extends TestCase
{
    /**
     * @expectedException \Akeneo\UserManagement\Component\Exception\ForbiddenToRemoveRoleException
     * @expectedExceptionMessage You can not delete this role, otherwise some users will no longer have a role.
     */
    public function testUnableToRemoveARoleIfUsersWillNoLongerHaveRole()
    {
        $adminRole = $this->get('pim_user.repository.role')->findOneByIdentifier('ROLE_ADMINISTRATOR');
        $this->get('pim_user.remover.role')->remove($adminRole);
    }

    public function testSuccessfullyToRemoveARole()
    {
        $adminRole = $this->get('pim_user.repository.role')->findOneByIdentifier('IS_AUTHENTICATED_ANONYMOUSLY');
        $this->get('pim_user.remover.role')->remove($adminRole);
        $this->assertNull($this->get('pim_user.repository.role')->findOneByIdentifier('IS_AUTHENTICATED_ANONYMOUSLY'));
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
