<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Controller;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\EnrichBundle\AbstractController\AbstractController;
use PimEnterprise\Bundle\UserBundle\Context\UserContext;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\WorkflowBundle\Manager\PublishedProductManager;

/**
 * Published product controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class PublishedProductController extends AbstractController
{
    /** @var UserContext */
    protected $userContext;

    /** @var PublishedProductManager */
    protected $manager;

    /** @var VersionManager */
    protected $versionManager;

    /**
     * @param Request                  $request
     * @param EngineInterface          $templating
     * @param RouterInterface          $router
     * @param SecurityContextInterface $securityContext
     * @param FormFactoryInterface     $formFactory
     * @param ValidatorInterface       $validator
     * @param TranslatorInterface      $translator
     * @param EventDispatcherInterface $eventDispatcher
     * @param UserContext              $userContext
     * @param PublishedProductManager  $manager
     * @param VersionManager           $versionManager
     */
    public function __construct(
        Request $request,
        EngineInterface $templating,
        RouterInterface $router,
        SecurityContextInterface $securityContext,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        EventDispatcherInterface $eventDispatcher,
        UserContext $userContext,
        PublishedProductManager $manager,
        VersionManager $versionManager
    ) {
        parent::__construct(
            $request,
            $templating,
            $router,
            $securityContext,
            $formFactory,
            $validator,
            $translator,
            $eventDispatcher
        );
        $this->userContext    = $userContext;
        $this->manager        = $manager;
        $this->versionManager = $versionManager;
    }

    /**
     * List of published products
     *
     * @param Request $request the request
     *
     * @AclAncestor("pimee_workflow_published_product_index")
     * @Template
     * @return array
     */
    public function indexAction(Request $request)
    {
        return [
            'locales'    => $this->getUserLocales(),
            'dataLocale' => $this->getDataLocale(),
        ];
    }

    /**
     * Publish a product
     *
     * @param Request $request
     * @param integer $id
     *
     * @Template
     * @AclAncestor("pimee_workflow_published_product_index")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function publishAction(Request $request, $id)
    {
        $product = $this->manager->findOriginalProduct($id);

        $isOwner = $this->securityContext->isGranted(Attributes::OWNER, $product);
        if (!$isOwner) {
            throw new AccessDeniedException();
        }

        $this->manager->publish($product);
        $this->addFlash('success', 'flash.product.published');

        return parent::redirectToRoute(
            'pim_enrich_product_edit',
            ['id' => $product->getId(), 'dataLocale' => $this->getDataLocale()]
        );
    }

    /**
     * Unpublish a product
     *
     * @param Request $request
     * @param integer $id
     *
     * @Template
     * @AclAncestor("pimee_workflow_published_product_index")
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function unpublishAction(Request $request, $id)
    {
        $published = $this->manager->findPublishedProductById($id);

        $isOwner = $this->securityContext->isGranted(Attributes::OWNER, $published->getOriginalProduct());
        if (!$isOwner) {
            throw new AccessDeniedException();
        }

        $this->manager->unpublish($published);
        $this->addFlash('success', 'flash.product.unpublished');

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'successful' => true,
                    'message' => $this->translator->trans('flash.product.unpublished')
                ]
            );
        }

        return parent::redirectToRoute(
            'pimee_workflow_published_product_index',
            ['dataLocale' => $this->getDataLocale()]
        );
    }

    /**
     * View a product
     *
     * @param Request $request
     * @param integer $id
     *
     * @Template
     * @AclAncestor("pimee_workflow_published_product_index")
     * @return array
     */
    public function viewAction(Request $request, $id)
    {
        $published = $this->manager->findPublishedProductById($id);
        $original = $published->getOriginalProduct();

        return [
            'published'  => $published,
            'dataLocale' => $this->getDataLocale(),
            'locales'    => $this->getUserLocales(),
            'created'    => $this->versionManager->getOldestLogEntry($original),
            'updated'    => $this->versionManager->getNewestLogEntry($original),
        ];
    }

    /**
     * Return only granted user locales
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\Locale[]
     */
    protected function getUserLocales()
    {
        return $this->userContext->getGrantedUserLocales();
    }

    /**
     * Get data locale code
     *
     * @return string
     */
    protected function getDataLocale()
    {
        return $this->userContext->getCurrentLocaleCode();
    }
}
