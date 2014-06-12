<?php

namespace PimEnterprise\Bundle\UserBundle\Context;

use Pim\Bundle\UserBundle\Context\UserContext as BaseUserContext;
use PimEnterprise\Bundle\CatalogBundle\Manager\CategoryManager;
use PimEnterprise\Bundle\SecurityBundle\Voter\CategoryVoter;

/**
 * User context that provides access to user locale, channel and default category tree
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class UserContext extends BaseUserContext
{
    /**
     * @var CategoryManager
     */
    protected $categoryManager;

    /**
     * Get user category tree
     *
     * @return CategoryInterface
     * @throws \LogicException
     */
    public function getAccessibleUserTree()
    {
        $defaultTree = $this->getUserOption('defaultTree');

        if ($defaultTree && $this->securityContext->isGranted(CategoryVoter::VIEW_PRODUCTS, $defaultTree)) {
            return $defaultTree;
        }

        $trees = $this->categoryManager->getAccessibleTrees($this->getUser());

        if (count($trees)) {
            return current($trees);
        }

        throw new \LogicException('User should have a default tree');
    }
}
