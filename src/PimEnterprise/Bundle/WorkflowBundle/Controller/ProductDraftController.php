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

use Doctrine\Common\Persistence\ObjectRepository;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\WorkflowBundle\Manager\ProductDraftManager;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraft;
use Pim\Bundle\EnrichBundle\AbstractController\AbstractController;
use Pim\Bundle\UserBundle\Context\UserContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * ProductDraft controller
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class ProductDraftController extends AbstractController
{
    /** @var ObjectRepository */
    protected $repository;

    /** @var ProductDraftManager */
    protected $manager;

    /** @var UserContext */
    protected $userContext;

    /**
     * @param Request                  $request
     * @param EngineInterface          $templating
     * @param RouterInterface          $router
     * @param SecurityContextInterface $securityContext
     * @param FormFactoryInterface     $formFactory
     * @param ValidatorInterface       $validator
     * @param TranslatorInterface      $translator
     * @param EventDispatcherInterface $eventDispatcher
     * @param ObjectRepository         $repository
     * @param ProductDraftManager      $manager
     * @param UserContext              $userContext
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
        ObjectRepository $repository,
        ProductDraftManager $manager,
        UserContext $userContext
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
        $this->repository  = $repository;
        $this->manager     = $manager;
        $this->userContext = $userContext;
    }

    /**
     * List proposals
     *
     * @Template
     * @return Response
     * @throws AccessDeniedException if the current user is not the owner of any categories
     */
    public function indexAction()
    {
        if (!$this->securityContext->isGranted(Attributes::OWN_AT_LEAST_ONE_CATEGORY)) {
            throw new AccessDeniedException();
        }

        return [];
    }

    /**
     * @param Request        $request
     * @param integer|string $id
     *
     * @return JsonResponse|RedirectResponse
     * @throws \LogicException
     * @throws NotFoundHttpException
     * @throws AccessDeniedHttpException
     */
    public function approveAction(Request $request, $id)
    {
        if (null === $productDraft = $this->repository->find($id)) {
            throw new NotFoundHttpException(sprintf('Product draft "%s" not found', $id));
        }

        if (ProductDraft::READY !== $productDraft->getStatus()) {
            throw new \LogicException('A product draft that is not ready can not be approved');
        }

        if (!$this->securityContext->isGranted(Attributes::OWN, $productDraft->getProduct())) {
            throw new AccessDeniedHttpException();
        }

        if (!$this->securityContext->isGranted(Attributes::EDIT_ATTRIBUTES, $productDraft)) {
            throw new AccessDeniedHttpException();
        }

        try {
            $this->manager->approve($productDraft);
            $status = 'success';
            $messageParams = [];
        } catch (ValidatorException $e) {
            $status = 'error';
            $messageParams = ['%error%' => $e->getMessage()];
        }

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'successful' => $status === 'success',
                    'message' => $this->getTranslator()->trans(
                        sprintf('flash.product_draft.approve.%s', $status),
                        $messageParams
                    )
                ]
            );
        }

        $this->addFlash($status, sprintf('flash.product_draft.approve.%s', $status), $messageParams);

        return $this->redirect(
            $this->generateUrl(
                'pim_enrich_product_edit',
                [
                    'id' => $productDraft->getProduct()->getId(),
                    'dataLocale' => $this->getCurrentLocaleCode()
                ]
            )
        );
    }

    /**
     * @param Request        $request
     * @param integer|string $id
     *
     * @return RedirectResponse
     * @throws NotFoundHttpException
     * @throws AccessDeniedHttpException
     */
    public function refuseAction(Request $request, $id)
    {
        if (null === $productDraft = $this->repository->find($id)) {
            throw new NotFoundHttpException(sprintf('Product draft "%s" not found', $id));
        }

        if (!$this->securityContext->isGranted(Attributes::OWN, $productDraft->getProduct())) {
            throw new AccessDeniedHttpException();
        }

        if (!$this->securityContext->isGranted(Attributes::EDIT_ATTRIBUTES, $productDraft)) {
            throw new AccessDeniedHttpException();
        }

        $this->manager->refuse($productDraft);

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'successful' => true,
                    'message' => $this->getTranslator()->trans('flash.product_draft.refuse.success')
                ]
            );
        }

        return $this->redirect(
            $this->generateUrl(
                'pim_enrich_product_edit',
                [
                    'id' => $productDraft->getProduct()->getId(),
                    'dataLocale' => $this->getCurrentLocaleCode()
                ]
            )
        );
    }

    /**
     * Mark a product draft as ready
     *
     * @param integer|string $id
     *
     * @return RedirectResponse
     * @throws NotFoundHttpException
     * @throws AccessDeniedHttpException
     */
    public function readyAction($id)
    {
        if (null === $productDraft = $this->repository->find($id)) {
            throw new NotFoundHttpException(sprintf('Product draft "%s" not found', $id));
        }

        if (!$this->securityContext->isGranted(Attributes::OWN, $productDraft)) {
            throw new AccessDeniedHttpException();
        }

        $this->manager->markAsReady($productDraft);

        return $this->redirect(
            $this->generateUrl(
                'pim_enrich_product_edit',
                [
                    'id' => $productDraft->getProduct()->getId(),
                    'dataLocale' => $this->getCurrentLocaleCode()
                ]
            )
        );
    }

    /**
     * Get data locale code
     *
     * @return string
     */
    protected function getCurrentLocaleCode()
    {
        return $this->userContext->getCurrentLocaleCode();
    }
}
