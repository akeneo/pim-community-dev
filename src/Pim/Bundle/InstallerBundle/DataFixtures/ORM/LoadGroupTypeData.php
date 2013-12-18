<?php

namespace Pim\Bundle\InstallerBundle\DataFixtures\ORM;

use Symfony\Component\Yaml\Yaml;

use Doctrine\Common\Persistence\ObjectManager;

use Pim\Bundle\CatalogBundle\Entity\GroupType;
use Pim\Bundle\InstallerBundle\DataFixtures\ORM\AbstractInstallerFixture;

/**
 * Load fixtures for group types
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LoadGroupTypeData extends AbstractInstallerFixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $configuration = Yaml::parse(realpath($this->getFilePath()));

        if (isset($configuration['group_types'])) {
            foreach ($configuration['group_types'] as $code => $data) {
                $type = $this->createType($code, $data);
                $this->validate($type, $data);
                $manager->persist($type);
                $this->addReference(get_class($type).'.'. $type->getCode(), $type);
            }
        }

        $manager->flush();
    }

    /**
     * Create a group type entity
     *
     * @param string $code
     * @param array  $data
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\GroupType
     */
    protected function createType($code, $data)
    {
        $type = new GroupType();
        $type->setCode($code);
        $type->setVariant($data['is_variant']);

        return $type;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return 'group_types';
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 150;
    }
}
