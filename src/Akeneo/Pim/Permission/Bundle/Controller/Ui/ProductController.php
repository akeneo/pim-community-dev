<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Bundle\Controller\Ui;

use Akeneo\Pim\Enrichment\Bundle\Controller\Ui\ProductController as BaseProductController;
use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Permission\Bundle\Persistence\ORM\Category\CategoryManager;
use Akeneo\Tool\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Collections\Collection;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Product Controller
 *
 * @author Filips Alpe <filips@akeneo.com>
 */
class ProductController extends BaseProductController
{
    protected CategoryManager $categoryManager;

    public function __construct(
        TranslatorInterface $translator,
        ProductRepositoryInterface $productRepository,
        CategoryRepositoryInterface $categoryRepository,
        SaverInterface $productSaver,
        string $categoryClass,
        SecurityFacade $securityFacade,
        string $acl,
        string $template,
        CategoryManager $categoryManager
    ) {
        parent::__construct(
            $translator,
            $productRepository,
            $categoryRepository,
            $productSaver,
            $categoryClass,
            $securityFacade,
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
