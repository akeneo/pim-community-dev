<?php

namespace PimEnterprise\Bundle\CatalogBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\SecurityContextInterface;

use Pim\Bundle\CatalogBundle\Manager\CategoryManager as BaseCategoryManager;
use PimEnterprise\Bundle\SecurityBundle\Voter\CategoryVoter;

/**
 * Category manager
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class CategoryManager extends BaseCategoryManager
{
    /**
     * @var SecurityContextInterface
     */
    protected $securityContext;

    /**
     * Constructor
     *
     * @param ObjectManager            $om
     * @param string                   $categoryClass
     * @param SecurityContextInterface $securityContext
     */
    public function __construct(ObjectManager $om, $categoryClass, SecurityContextInterface $securityContext)
    {
        parent::__construct($om, $categoryClass);

        $this->securityContext = $securityContext;
    }

    /**
     * Get the trees accessible by the current user.
     *
     * @return array
     */
    public function getAccessibleTrees()
    {
        $trees = [];

        foreach ($this->getTrees() as $tree) {
            if ($this->securityContext->isGranted(CategoryVoter::VIEW_PRODUCTS, $tree)) {
                $trees[] = $tree;
            }
        }

        return $trees;
    }
}
