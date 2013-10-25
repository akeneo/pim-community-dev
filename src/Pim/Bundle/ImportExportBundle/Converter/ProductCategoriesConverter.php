<?php

namespace Pim\Bundle\ImportExportBundle\Converter;

use Doctrine\ORM\EntityManager;

/**
 * Convert a basic representation of a category into a complex one bindable on a product form
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductCategoriesConverter
{
    const CATEGORIES_KEY = '[categories]';

    protected $entityManager;

    /**
     * Constructor
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function convert($data)
    {
        if (null !== $ids = $this->getCategoryIds($data)) {
            return array('categories' => $ids);
        }

        return array();
    }

    /**
     * Get the category ids
     *
     * @param array $data The submitted data
     *
     * @return int|null null if the self::CATEGORIES_KEY wasn't sent in the data or the category code doesn't exist
     */
    private function getCategoryIds(array $data)
    {
        if (!array_key_exists(self::CATEGORIES_KEY, $data)) {
            // TODO Warn that the categories could not be determined
            return null;
        }

        $ids = array();
        foreach (explode(',', $data[self::CATEGORIES_KEY]) as $code) {
            $category = $this->getCategory($code);
            if (!$category) {
                throw new \InvalidArgumentException(
                    sprintf('Couldn\'t find a category with code "%s"', $code)
                );
            }
            $ids[] = $category->getId();
        }

        return $ids;
    }

    /**
     * Get a category by code
     *
     * @param string $code
     *
     * @return Category|null
     */
    private function getCategory($code)
    {
        return $this->entityManager
            ->getRepository('PimCatalogBundle:Category')
            ->findOneBy(array('code' => $code));
    }
}
