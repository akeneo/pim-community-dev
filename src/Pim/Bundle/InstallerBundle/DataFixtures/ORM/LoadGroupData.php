<?php

namespace Pim\Bundle\InstallerBundle\DataFixtures\ORM;

use Symfony\Component\Yaml\Yaml;

use Doctrine\Common\Persistence\ObjectManager;

use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Entity\GroupTranslation;
use Pim\Bundle\InstallerBundle\DataFixtures\ORM\AbstractInstallerFixture;

/**
 * Load fixtures for product groups
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LoadGroupData extends AbstractInstallerFixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $configuration = Yaml::parse(realpath($this->getFilePath()));

        if (isset($configuration['groups'])) {
            foreach ($configuration['groups'] as $code => $data) {
                $group = $this->createGroup($code, $data);
                $this->validate($group, $data);
                $manager->persist($group);
                $this->addReference('group.'. $group->getCode(), $group);
            }
        }

        $manager->flush();
    }

    /**
     * Create a group entity
     *
     * @param string $code
     * @param array  $data
     *
     * @return Group
     */
    protected function createGroup($code, $data)
    {
        $type = $this->getReference('Pim\Bundle\CatalogBundle\Entity\GroupType.'. $data['type']);
        $group = new Group();
        $group->setCode($code);
        $group->setType($type);

        if (isset($data['labels'])) {
            foreach ($data['labels'] as $locale => $translation) {
                $this->createTranslation($group, $locale, $translation);
            }
        }

        if (isset($data['attributes'])) {
            foreach ($data['attributes'] as $attribute) {
                $group->addAttribute($this->getReference('Pim\Bundle\CatalogBundle\Entity\ProductAttribute.'. $attribute));
            }
        }

        return $group;
    }

    /**
     * Create a translation entity
     *
     * @param Group  $group
     * @param string $locale
     * @param string $content
     */
    protected function createTranslation($group, $locale, $content)
    {
        $translation = new GroupTranslation();
        $translation->setForeignKey($group);
        $translation->setLocale($locale);
        $translation->setLabel($content);

        $group->addTranslation($translation);
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return 'groups';
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 160;
    }
}
