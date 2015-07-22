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

use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraft;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Doctrine\Common\Persistence\ObjectRepository;
use Pim\Bundle\EnrichBundle\AbstractController\AbstractController;
use Pim\Bundle\UserBundle\Context\UserContext;
use PimEnterprise\Bundle\WorkflowBundle\Manager\ProductDraftManager;
use PimEnterprise\Bundle\SecurityBundle\Attributes;

/**
 * ProductDraft controller
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
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
     * @param integer|string $id
     *
     * @throws \LogicException
     * @throws NotFoundHttpException
     * @throws AccessDeniedHttpException
     *
     * @return RedirectResponse
     */
    public function approveAction($id)
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
            $this->addFlash('success', 'flash.product_draft.approve.success');
        } catch (ValidatorException $e) {
            $this->addFlash('error', 'flash.product_draft.approve.error', ['%error%' => $e->getMessage()]);
        }

        return $this->redirect(
            $this->generateUrl(
                'pim_enrich_product_edit',
                [
                    'id'         => $productDraft->getProduct()->getId(),
                    'dataLocale' => $this->getCurrentLocaleCode()
                ]
            )
        );
    }

    /**
     * @param integer|string $id
     *
     * @throws NotFoundHttpException
     * @throws AccessDeniedHttpException
     *
     * @return RedirectResponse
     */
    public function refuseAction($id)
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

        return $this->redirect(
            $this->generateUrl(
                'pim_enrich_product_edit',
                [
                    'id'         => $productDraft->getProduct()->getId(),
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
     * @throws NotFoundHttpException
     * @throws AccessDeniedHttpException
     *
     * @return RedirectResponse
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
                    'id'         => $productDraft->getProduct()->getId(),
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
