<?php

namespace Pim\Bundle\InstallerBundle\DataFixtures\ORM;

use Symfony\Component\Yaml\Yaml;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Entity\Category;

/**
 * Load fixtures for categories
 *
 * @author    Nicolas Dupont <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LoadCategoryData extends AbstractInstallerFixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $configuration = Yaml::parse(realpath($this->getFilePath()));

        foreach ($configuration['categories'] as $data) {
            $category = $this->createCategory($data['code']);
            $manager->persist($category);
        }

        $manager->flush();
    }

    /**
     * Create a category
     * @param string   $code       Category code
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\Category
     */
    protected function createCategory($code)
    {
        $category = new Category();
        $category->setCode($code);
        $this->setReference('category.'. $code, $category);

        return $category;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return 'categories';
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 2;
    }
}
