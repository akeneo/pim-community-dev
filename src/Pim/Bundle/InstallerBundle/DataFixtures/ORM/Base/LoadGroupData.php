<?php

namespace Pim\Bundle\InstallerBundle\DataFixtures\ORM\Base;

use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\UserBundle\Entity\Group;
use Pim\Bundle\InstallerBundle\DataFixtures\ORM\AbstractInstallerFixture;
use Symfony\Component\Yaml\Yaml;

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
        $dataGroups = Yaml::parse(file_get_contents(realpath($this->getFilePath())));
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

        return $group;
    }
}
