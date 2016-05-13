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
use Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Collections\Collection;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Bundle\EnrichBundle\Controller\ProductController as BaseProductController;
use Pim\Bundle\EnrichBundle\Flash\Message;
use Pim\Bundle\EnrichBundle\Manager\SequentialEditManager;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\CategoryInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use PimEnterprise\Bundle\CatalogBundle\Manager\CategoryManager;
use PimEnterprise\Bundle\UserBundle\Context\UserContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Product Controller
 *
 * @author Filips Alpe <filips@akeneo.com>
 */
class ProductController extends BaseProductController
{
    /** @var CategoryManager */
    protected $categoryManager;

    public function __construct(
        Request $request,
        EngineInterface $templating,
        RouterInterface $router,
        TokenStorageInterface $tokenStorage,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        EventDispatcherInterface $eventDispatcher,
        ProductRepositoryInterface $productRepository,
        CategoryRepositoryInterface $categoryRepository,
        UserContext $userContext,
        VersionManager $versionManager,
        SecurityFacade $securityFacade,
        SaverInterface $productSaver,
        SequentialEditManager $seqEditManager,
        ProductBuilderInterface $productBuilder,
        SimpleFactoryInterface $categoryFactory,
        CategoryManager $categoryManager,
        $categoryClass
    ) {
        parent::__construct(
            $request,
            $templating,
            $router,
            $tokenStorage,
            $formFactory,
            $validator,
            $translator,
            $eventDispatcher,
            $productRepository,
            $categoryRepository,
            $userContext,
            $versionManager,
            $securityFacade,
            $productSaver,
            $seqEditManager,
            $productBuilder,
            $categoryFactory,
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
     * @return Response|RedirectResponse
     */
    public function indexAction()
    {
        try {
            $this->userContext->getAccessibleUserTree();
        } catch (\LogicException $e) {
            $this->request->getSession()->getFlashBag()
                ->add('error', new Message('category.permissions.no_access_to_products'));

            return $this->redirectToRoute('oro_default');
        }

        if (null === $dataLocale = $this->getDataLocale()) {
            $this->request->getSession()->getFlashBag()
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
}
