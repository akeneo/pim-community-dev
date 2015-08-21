<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Controller;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Manager\CompletenessManager;
use Pim\Bundle\EnrichBundle\AbstractController\AbstractController;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\UserBundle\Context\UserContext;
use PimEnterprise\Bundle\WorkflowBundle\Manager\PublishedProductManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Published product controller
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class PublishedProductController extends AbstractController
{
    /** @var UserContext */
    protected $userContext;

    /** @var PublishedProductManager */
    protected $manager;

    /** @var CompletenessManager */
    protected $completenessManager;

    /** @var ChannelManager */
    protected $channelManager;

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /**
     * @param Request                       $request
     * @param EngineInterface               $templating
     * @param RouterInterface               $router
     * @param TokenStorageInterface         $tokenStorage
     * @param FormFactoryInterface          $formFactory
     * @param ValidatorInterface            $validator
     * @param TranslatorInterface           $translator
     * @param EventDispatcherInterface      $eventDispatcher
     * @param UserContext                   $userContext
     * @param PublishedProductManager       $manager
     * @param CompletenessManager           $completenessManager
     * @param ChannelManager                $channelManager
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(
        Request $request,
        EngineInterface $templating,
        RouterInterface $router,
        TokenStorageInterface $tokenStorage,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        EventDispatcherInterface $eventDispatcher,
        UserContext $userContext,
        PublishedProductManager $manager,
        CompletenessManager $completenessManager,
        ChannelManager $channelManager,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        parent::__construct(
            $request,
            $templating,
            $router,
            $tokenStorage,
            $formFactory,
            $validator,
            $translator,
            $eventDispatcher
        );

        $this->userContext          = $userContext;
        $this->manager              = $manager;
        $this->completenessManager  = $completenessManager;
        $this->channelManager       = $channelManager;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * List of published products
     *
     * @param Request $request the request
     *
     * @AclAncestor("pimee_workflow_published_product_index")
     * @Template
     *
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
     * Unpublish a product
     *
     * @param Request    $request
     * @param int|string $id
     *
     * @Template
     * @AclAncestor("pimee_workflow_published_product_index")
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function unpublishAction(Request $request, $id)
    {
        $published = $this->findPublishedOr404($id);

        $isOwner = $this->authorizationChecker->isGranted(Attributes::OWN, $published->getOriginalProduct());
        if (!$isOwner) {
            throw new AccessDeniedException();
        }

        $this->manager->unpublish($published);

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'successful' => true,
                    'message'    => $this->translator->trans('flash.product.unpublished')
                ]
            );
        }

        $this->addFlash('success', 'flash.product.unpublished');

        return parent::redirectToRoute(
            'pimee_workflow_published_product_index',
            ['dataLocale' => $this->getDataLocale()]
        );
    }

    /**
     * View a published product
     *
     * @param Request    $request
     * @param int|string $id
     *
     * @Template
     * @AclAncestor("pimee_workflow_published_product_index")
     *
     * @return array
     */
    public function viewAction($id)
    {
        return ['productId' => $id];
    }

    /**
     * Displays completeness for a published product
     *
     * @param int|string $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function completenessAction($id)
    {
        $published = $this->findPublishedOr404($id);
        $channels = $this->channelManager->getFullChannels();
        $locales = $this->userContext->getUserLocales();

        $completenesses = $this->completenessManager->getProductCompleteness(
            $published,
            $channels,
            $locales,
            $this->getDataLocale()
        );

        return $this->templating->renderResponse(
            'PimEnrichBundle:Completeness:_completeness.html.twig',
            [
                'product'        => $published,
                'channels'       => $channels,
                'locales'        => $locales,
                'completenesses' => $completenesses
            ]
        );
    }

    /**
     * Find a published product by its id or return a 404 response
     *
     * @param int|string $id
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return \PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductInterface
     */
    protected function findPublishedOr404($id)
    {
        $published = $this->manager->findPublishedProductById($id);

        if (!$published) {
            throw $this->createNotFoundException(
                sprintf('Published product with id %s could not be found.', (string) $id)
            );
        }

        return $published;
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
