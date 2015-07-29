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

use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Manager\CategoryManager;
use Pim\Bundle\CatalogBundle\Manager\MediaManager;
use Pim\Bundle\CatalogBundle\Manager\ProductCategoryManager;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\EnrichBundle\Controller\ProductController as BaseProductController;
use Pim\Bundle\EnrichBundle\Manager\SequentialEditManager;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\UserBundle\Context\UserContext;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
     * Dispatch to product view or product edit when a user click on a product grid row
     *
     * @param Request $request
     * @param int     $id
     *
     * @throws AccessDeniedException
     *
     * @return RedirectResponse
     *
     * @AclAncestor("pim_enrich_product_index")
     */
    public function dispatchAction(Request $request, $id)
    {
        $product = $this->findProductOr404($id);
        $editProductGranted = $this->securityFacade->isGranted(Attributes::EDIT, $product);

        if ($editProductGranted) {
            return $this->render('PimEnrichBundle:Product:edit.html.twig', ['productId' => $id]);
        } elseif ($this->securityFacade->isGranted(Attributes::VIEW, $product)) {
            $parameters = $this->showAction($this->request, $id);

            return $this->render('PimEnrichBundle:Product:show.html.twig', $parameters);
        }

        throw new AccessDeniedException();
    }

    /**
     * Show product
     *
     * @param Request $request
     * @param int     $id
     *
     * @Template
     * @AclAncestor("pim_enrich_product_index")
     *
     * @return array
     */
    public function showAction(Request $request, $id)
    {
        $product = $this->findProductOr404($id);
        $locale  = $this->userContext->getCurrentLocale();

        $viewLocaleGranted = $this->securityFacade->isGranted(Attributes::VIEW_PRODUCTS, $locale);
        if (!$viewLocaleGranted) {
            throw new AccessDeniedException();
        }

        $sequentialEdit = $this->seqEditManager->findByUser($this->getUser());
        if ($sequentialEdit) {
            $this->seqEditManager->findWrap($sequentialEdit, $product);
        }

        return [
            'product'          => $product,
            'dataLocale'       => $this->getDataLocaleCode(),
            'locales'          => $this->getUserLocales(),
            'comparisonLocale' => $this->getComparisonLocale(),
            'created'          => $this->versionManager->getOldestLogEntry($product),
            'updated'          => $this->versionManager->getNewestLogEntry($product),
            'sequentialEdit'   => $sequentialEdit,
        ];
    }

    /**
     * Show a product value
     *
     * @param Request $request
     * @param string  $productId
     * @param string  $attributeCode
     *
     * @return Response
     */
    public function showAttributeAction(Request $request, $productId, $attributeCode)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $product = $this->findProductOr404($productId);
        $locale = $request->query->get('locale');
        $scope = $request->query->get('scope');

        $value = $product->getValue($attributeCode, $locale, $scope);

        return new Response((string) $value);
    }

    /**
     * Drafts of a product
     *
     * @param Request $request
     * @param int     $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function draftsAction(Request $request, $id)
    {
        return $this->render(
            'PimEnterpriseEnrichBundle:Product:_product_drafts.html.twig',
            [
                'product'    => $this->findProductOr404($id),
                'dataLocale' => $this->getDataLocaleCode()
            ]
        );
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

    /**
     * Switch case to redirect after saving a product from the edit form
     *
     * {@inheritdoc}
     */
    protected function redirectAfterEdit($params)
    {
        if ($this->getRequest()->get('action') == self::SAVE_AND_NEXT) {
            $route          = 'pimee_enrich_product_dispatch';
            $sequentialEdit = $this->seqEditManager->findByUser($this->getUser());
            $params['id']   = $sequentialEdit->getNextId($params['id']);
        } else {
            return parent::redirectAfterEdit($params);
        }

        return $this->redirectToRoute($route, $params);
    }
}
