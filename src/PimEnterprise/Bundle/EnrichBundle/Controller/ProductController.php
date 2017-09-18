<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\EnrichBundle\Controller;

use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Collections\Collection;
use Pim\Bundle\EnrichBundle\Controller\ProductController as BaseProductController;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\CategoryInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use Pim\Component\Catalog\ValuesFiller\EntityWithFamilyValuesFillerInterface;
use PimEnterprise\Bundle\CatalogBundle\Manager\CategoryManager;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Product Controller
 *
 * @author Filips Alpe <filips@akeneo.com>
 */
class ProductController extends BaseProductController
{
    /** @var CategoryManager */
    protected $categoryManager;

    /**
     * @param RouterInterface                       $router
     * @param TokenStorageInterface                 $tokenStorage
     * @param FormFactoryInterface                  $formFactory
     * @param TranslatorInterface                   $translator
     * @param ProductRepositoryInterface            $productRepository
     * @param CategoryRepositoryInterface           $categoryRepository
     * @param UserContext                           $userContext
     * @param SecurityFacade                        $securityFacade
     * @param SaverInterface                        $productSaver
     * @param SequentialEditManager                 $seqEditManager
     * @param ProductBuilderInterface               $productBuilder
     * @param EntityWithFamilyValuesFillerInterface $valuesFiller
     * @param CategoryManager                       $categoryManager
     * @param                                       $categoryClass
     */
    public function __construct(
        TranslatorInterface $translator,
        ProductRepositoryInterface $productRepository,
        CategoryRepositoryInterface $categoryRepository,
        SaverInterface $productSaver,
        ProductBuilderInterface $productBuilder,
        EntityWithFamilyValuesFillerInterface $valuesFiller,
        CategoryManager $categoryManager,
        $categoryClass
    ) {
        parent::__construct(
            $translator,
            $productRepository,
            $categoryRepository,
            $productSaver,
            $productBuilder,
            $valuesFiller,
            $categoryClass
        );

        $this->categoryManager = $categoryManager;
    }

    /**
     * Override to get only the granted path for the filled tree
     *
     * {@inheritdoc}
     */
    protected function getFilledTree(CategoryInterface $parent, Collection $categories)
    {
        return $this->categoryManager->getGrantedFilledTree($parent, $categories);
    }
}
