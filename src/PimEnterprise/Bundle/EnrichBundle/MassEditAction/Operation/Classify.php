<?php

namespace PimEnterprise\Bundle\EnrichBundle\MassEditAction\Operation;

use Pim\Bundle\EnrichBundle\MassEditAction\Operation\Classify as BaseClassify;
use PimEnterprise\Bundle\CatalogBundle\Manager\CategoryManager;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Batch operation to classify products
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class Classify extends BaseClassify
{
    /**
     * @var SecurityContextInterface
     */
    protected $securityContext;

    /**
     * @param CategoryManager          $categoryManager
     * @param SecurityContextInterface $securityContext
     */
    public function __construct(CategoryManager $categoryManager, SecurityContextInterface $securityContext)
    {
        $this->securityContext = $securityContext;
        $this->categoryManager = $categoryManager;
        $this->trees           = $categoryManager->getAccessibleTrees($securityContext->getToken()->getUser());
        $this->categories      = [];
    }
}
