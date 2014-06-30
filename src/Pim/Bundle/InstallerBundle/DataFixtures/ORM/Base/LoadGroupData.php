<?php

namespace Pim\Bundle\InstallerBundle\DataFixtures\ORM\Base;

use Pim\Bundle\InstallerBundle\DataFixtures\ORM\AbstractInstallerFixture;
use Symfony\Component\Yaml\Yaml;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\UserBundle\Entity\Group;

/**
 * Load fixtures for groups
 *
 * @author    nicolas dupont <nicolas@akeneo.com>
 * @copyright 2014 akeneo sas (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  open software license (osl 3.0)
 */
class LoadGroupData extends AbstractInstallerFixture
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
        $dataGroups = Yaml::parse(realpath($this->getFilePath()));
        foreach ($dataGroups['user_groups'] as $dataGroup) {
            $group = $this->buildGroup($dataGroup);
            $manager->persist($group);
        }
        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return 'user_groups';
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 16;
    }

    /**
     * Build the group entity from data
     *
     * @param array $data
     *
     * @return Group
     */
    protected function buildGroup(array $data)
    {
        $name = $data['name'];
        $group = new Group($name);
        $owner = isset($data['owner']) ? $data['owner'] : 'Main';
        $owner = $this->getOwner($owner);
        $group->setOwner($owner);

        return $group;
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
