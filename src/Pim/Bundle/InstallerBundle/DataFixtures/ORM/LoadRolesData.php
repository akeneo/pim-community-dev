<?php

namespace Pim\Bundle\InstallerBundle\DataFixtures\ORM;

use Symfony\Component\Yaml\Yaml;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\UserBundle\Entity\Role;

/**
 * Load fixtures for roles
 *
 * @author    nicolas dupont <nicolas@akeneo.com>
 * @copyright 2014 akeneo sas (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  open software license (osl 3.0)
 */
class LoadRoleData extends AbstractInstallerFixture
{
    /**
     * @var ObjectManager
     */
    protected $om;

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->om = $manager;
        $dataRoles = Yaml::parse(realpath($this->getFilePath()));
        foreach ($dataRoles['roles'] as $code => $dataRole) {
            $dataRole['role']= $code;
            $role = $this->buildRole($dataRole);
            $manager->persist($role);
        }
        $manager->flush();
    }

    /**
     * Build the role entity from data
     *
     * @param array $data
     *
     * @return Role
     */
    protected function buildRole(array $data)
    {
        $code = $data['code'];
        $label = $data['label'];
        $role = new Role($code);
        $role->setLabel($label);
        $owner = isset($data['owner']) ? $data['owner'] : 'Main';
        $owner = $this->getOwner($owner);
        $role->setOwner($owner);

        return $role;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return 'roles';
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 105;
    }

    /**
     * Get the owner (business unit) from code
     *
     * @param string $owner
     *
     * @return \Oro\Bundle\OrganizationBundle\Entity\BusinessUnit
     */
    protected function getOwner($owner)
    {
        return $this->om
            ->getRepository('OroOrganizationBundle:BusinessUnit')
            ->findOneBy(array('name' => $owner));
    }
}
