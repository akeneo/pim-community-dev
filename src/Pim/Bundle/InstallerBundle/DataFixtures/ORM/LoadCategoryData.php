<?php

namespace Pim\Bundle\InstallerBundle\DataFixtures\ORM;

use Symfony\Component\Yaml\Yaml;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Entity\Category;
use Pim\Bundle\CatalogBundle\Entity\CategoryTranslation;

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

        foreach ($configuration['categories'] as $code => $data) {
            $category = $this->createCategory($code, $data);
            $this->validate($category, $data);
            $manager->persist($category);
        }
        $manager->flush();
    }

    /**
     * Create a category
     * @param string $code
     * @param array  $data
     *
     * @return Category
     */
    protected function createCategory($code, array $data)
    {
        $category = new Category();
        $category->setCode($code);
        $this->setReference(get_class($category).'.'. $code, $category);

        foreach ($data['labels'] as $locale => $label) {
            $this->createLabel($category, $locale, $label);
        }

        if (isset($data['parent'])) {
            $parent = $this->getReference(get_class($category) . '.' . $data['parent']);
            $category->setParent($parent);
        }

        return $category;
    }

    /**
     * Creates a label
     *
     * @param Category $category
     * @param type     $locale
     * @param type     $label
     */
    protected function createLabel(Category $category, $locale, $label)
    {
        $translation = new CategoryTranslation();
        $translation
            ->setLabel($label)
            ->setLocale($locale)
            ->setForeignKey($category);
        $category->addTranslation($translation);
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
