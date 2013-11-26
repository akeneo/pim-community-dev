<?php

namespace Pim\Bundle\InstallerBundle\DataFixtures\ORM;

use Symfony\Component\Yaml\Yaml;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Entity\Association;
use Pim\Bundle\CatalogBundle\Entity\AssociationTranslation;
use Pim\Bundle\InstallerBundle\DataFixtures\ORM\AbstractInstallerFixture;

/**
 * Load fixtures for product associations
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LoadAssociationData extends AbstractInstallerFixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $configuration = Yaml::parse(realpath($this->getFilePath()));

        if (isset($configuration['associations'])) {
            foreach ($configuration['associations'] as $code => $data) {
                $association = $this->createAssociation($code, $data);
                $this->validate($association, $data);
                $manager->persist($association);
                $this->addReference('association.'. $association->getCode(), $association);
            }
        }

        $manager->flush();
    }

    /**
     * Create an association entity
     *
     * @param string $code
     * @param array  $data
     *
     * @return Association
     */
    protected function createAssociation($code, $data)
    {
        $association = new Association();
        $association->setCode($code);

        if (isset($data['labels'])) {
            foreach ($data['labels'] as $locale => $translation) {
                $this->createTranslation($association, $locale, $translation);
            }
        }

        return $association;
    }

    /**
     * Create an association translation entity
     *
     * @param Association $association
     * @param string      $locale
     * @param string      $label
     */
    protected function createTranslation($association, $locale, $label)
    {
        $translation = new AssociationTranslation();
        $translation->setForeignKey($association);
        $translation->setLocale($locale);
        $translation->setLabel($label);

        $association->addTranslation($translation);
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return 'associations';
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 170;
    }
}
