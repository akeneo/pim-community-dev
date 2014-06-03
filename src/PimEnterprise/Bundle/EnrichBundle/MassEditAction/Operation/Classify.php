<?php

namespace PimEnterprise\Bundle\EnrichBundle\MassEditAction\Operation;

use Pim\Bundle\EnrichBundle\MassEditAction\Operation\Classify as BaseClassify;
use PimEnterprise\Bundle\CatalogBundle\Manager\CategoryManager;

/**
 * Batch operation to classify products
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class Classify extends BaseClassify
{
    /**
     * @param CategoryManager $categoryManager
     */
    public function __construct(CategoryManager $categoryManager)
    {
        $this->categoryManager = $categoryManager;
        $this->trees           = $categoryManager->getAccessibleTrees();
        $this->categories      = array();
    }
} 
