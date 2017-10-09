<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository;

use Akeneo\Bundle\ClassificationBundle\Doctrine\ORM\Repository\AbstractItemCategoryRepository;
use Doctrine\ORM\EntityManager;
use Pim\Component\Catalog\Repository\ProductModelCategoryRepositoryInterface;

/**
 * Product model category repository
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelCategoryRepository extends AbstractItemCategoryRepository implements ProductModelCategoryRepositoryInterface
{
    /** @var string */
    protected $categoryClass;

    /**
     * @param EntityManager $em
     * @param string        $entityName
     * @param string        $categoryClass
     */
    public function __construct(EntityManager $em, $entityName, $categoryClass)
    {
        parent::__construct($em, $entityName);

        $this->categoryClass = $categoryClass;
    }
}
