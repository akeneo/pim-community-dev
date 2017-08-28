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
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Pim\Bundle\EnrichBundle\Controller\ProductController as BaseProductController;
use Pim\Bundle\EnrichBundle\Flash\Message;
use Pim\Bundle\EnrichBundle\Manager\SequentialEditManager;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\CategoryInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use Pim\Component\Catalog\ValuesFiller\EntityWithFamilyValuesFillerInterface;
use PimEnterprise\Bundle\CatalogBundle\Manager\CategoryManager;
use PimEnterprise\Bundle\UserBundle\Context\UserContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
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
        RouterInterface $router,
        TokenStorageInterface $tokenStorage,
        FormFactoryInterface $formFactory,
        TranslatorInterface $translator,
        ProductRepositoryInterface $productRepository,
        CategoryRepositoryInterface $categoryRepository,
        UserContext $userContext,
        SecurityFacade $securityFacade,
        SaverInterface $productSaver,
        SequentialEditManager $seqEditManager,
        ProductBuilderInterface $productBuilder,
        EntityWithFamilyValuesFillerInterface $valuesFiller,
        CategoryManager $categoryManager,
        $categoryClass
    ) {
        parent::__construct(
            $router,
            $tokenStorage,
            $formFactory,
            $translator,
            $productRepository,
            $categoryRepository,
            $userContext,
            $securityFacade,
            $productSaver,
            $seqEditManager,
            $productBuilder,
            $valuesFiller,
            $categoryClass
        );

        $this->categoryManager = $categoryManager;
    }

    /**
     * {@inheritdoc}
     *
     * @AclAncestor("pim_enrich_product_index")
     * @Template
     *
     * @return []
     */
    public function indexAction(Request $request)
    {
        try {
            $this->userContext->getAccessibleUserTree();
        } catch (\LogicException $e) {
            $request->getSession()->getFlashBag()
                ->add('error', new Message('category.permissions.no_access_to_products'));

            return $this->redirectToRoute('oro_default');
        }

        if (null === $dataLocale = $this->getDataLocale()) {
            $request->getSession()->getFlashBag()
                ->add('error', new Message('locale.permissions.no_access_to_products'));

            return $this->redirectToRoute('oro_default');
        }

        $this->seqEditManager->removeByUser($this->tokenStorage->getToken()->getUser());

        return [
            'locales'    => $this->getUserLocales(),
            'dataLocale' => $dataLocale,
        ];
    }

    /**
     * Override to return only granted user locales
     *
     * @return LocaleInterface[]
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
}
