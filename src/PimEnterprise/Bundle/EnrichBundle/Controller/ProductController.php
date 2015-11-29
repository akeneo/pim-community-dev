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

use Doctrine\Common\Collections\Collection;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\EnrichBundle\Controller\ProductController as BaseProductController;
use Pim\Component\Catalog\Model\CategoryInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\UserBundle\Context\UserContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Product Controller
 *
 * @author Filips Alpe <filips@akeneo.com>
 */
class ProductController extends BaseProductController
{
    /** @var UserContext */
    protected $userContext;

    /**
     * {@inheritdoc}
     *
     * @AclAncestor("pim_enrich_product_index")
     * @Template
     *
     * @return Response|RedirectResponse
     */
    public function indexAction(Request $request)
    {
        try {
            $this->userContext->getAccessibleUserTree();
        } catch (\LogicException $e) {
            $this->addFlash('error', 'category.permissions.no_access_to_products');

            return $this->redirectToRoute('oro_default');
        }

        if (null === $dataLocale = $this->getDataLocale()) {
            $this->addFlash('error', 'locale.permissions.no_access_to_products');

            return $this->redirectToRoute('oro_default');
        }

        $this->seqEditManager->removeByUser($this->getUser());

        return [
            'locales'    => $this->getUserLocales(),
            'dataLocale' => $dataLocale,
        ];
    }

    /**
     * Override to return only granted user locales
     *
     * @return Locale[]
     */
    protected function getUserLocales()
    {
        return $this->userContext->getGrantedUserLocales();
    }

    /**
     * Returns the the data locale object
     * If user doesn't have permissions to see product data in any locale, returns null
     *
     * @return string|null
     */
    protected function getDataLocale()
    {
        try {
            return $this->userContext->getCurrentGrantedLocale();
        } catch (\LogicException $e) {
            return null;
        }
    }

    /**
     * Returns the code of the data locale
     * If user doesn't have permissions to see product data in any locale, returns null
     *
     * @return string|null
     */
    protected function getDataLocaleCode()
    {
        try {
            return $this->userContext->getCurrentGrantedLocale()->getCode();
        } catch (\LogicException $e) {
            return null;
        }
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

    /**
     * Override to get only the granted count for the granted tree
     *
     * {@inheritdoc}
     */
    protected function getProductCountByTree(ProductInterface $product)
    {
        return $this->productCatManager->getProductCountByGrantedTree($product);
    }
}
