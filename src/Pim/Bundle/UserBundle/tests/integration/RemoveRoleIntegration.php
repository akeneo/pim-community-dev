<?php

namespace Pim\Bundle\UserBundle\test\integration;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

class RemoveRoleIntegration extends TestCase
{
    /**
     * @expectedException \Pim\Component\User\Exception\ForbiddenToRemoveRoleException
     * @expectedExceptionMessage You can not delete this role, otherwise some users will no longer have a role.
     */
    public function testUnableToRemoveARoleIfUsersWillNoLongerHaveRole()
    {
        $adminRole = $this->get('pim_user.repository.role')->findOneByIdentifier('ROLE_ADMINISTRATOR');
        $this->get('pim_user.remover.role')->remove($adminRole);
    }

    public function testSuccessfullyToRemoveARole()
    {
        $adminRole = $this->get('pim_user.repository.role')->findOneByIdentifier('ROLE_USER');
        $this->get('pim_user.remover.role')->remove($adminRole);
        $this->assertNull($this->get('pim_user.repository.role')->findOneByIdentifier('ROLE_USER'));
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return new Configuration([Configuration::getMinimalCatalogPath()]);
    }
}
