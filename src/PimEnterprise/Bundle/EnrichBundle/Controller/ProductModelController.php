<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\EnrichBundle\Controller;

use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Doctrine\Common\Collections\Collection;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Pim\Bundle\EnrichBundle\Controller\ProductModelController as BaseProductModelController;
use Pim\Component\Catalog\Model\CategoryInterface;
use Pim\Component\Catalog\Repository\ProductModelRepositoryInterface;
use Pim\Component\Catalog\ValuesFiller\EntityWithFamilyValuesFillerInterface;
use PimEnterprise\Bundle\CatalogBundle\Manager\CategoryManager;

/**
 * Product model controller
 *
 * @author Julien Janvier <j.janvier@gmail.com>
 */
class ProductModelController extends BaseProductModelController
{
    /** @var CategoryManager */
    protected $categoryManager;

    /**
     * @param ProductModelRepositoryInterface       $productModelRepository
     * @param EntityWithFamilyValuesFillerInterface $valuesFiller
     * @param CategoryRepositoryInterface           $categoryRepository
     * @param SecurityFacade                        $securityFacade
     * @param string                                $categoryClass
     * @param string                                $acl
     * @param string                                $template
     * @param CategoryManager                       $categoryManager
     */
    public function __construct(
        ProductModelRepositoryInterface $productModelRepository,
        EntityWithFamilyValuesFillerInterface $valuesFiller,
        CategoryRepositoryInterface $categoryRepository,
        SecurityFacade $securityFacade,
        string $categoryClass,
        string $acl,
        string $template,
        CategoryManager $categoryManager
    ) {
        parent::__construct(
            $productModelRepository,
            $valuesFiller,
            $categoryRepository,
            $securityFacade,
            $categoryClass,
            $acl,
            $template
        );

        $this->categoryManager = $categoryManager;
    }

    /**
     * Override to get only the granted path for the filled tree
     *
     * {@inheritdoc}
     */
    protected function getFilledTree(CategoryInterface $parent, Collection $categories): array
    {
        return $this->categoryManager->getGrantedFilledTree($parent, $categories);
    }
}
