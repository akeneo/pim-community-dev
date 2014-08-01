<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Controller;

use PimEnterprise\Bundle\WorkflowBundle\Model\Proposition;
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
 * Proposition controller
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
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
     * @param ProductDraftManager       $manager
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
     * @return RedirectResponse
     * @throws \LogicException
     * @throws NotFoundHttpException
     * @throws AccessDeniedHttpException
     */
    public function approveAction($id)
    {
        if (null === $proposition = $this->repository->find($id)) {
            throw new NotFoundHttpException(sprintf('Proposition "%s" not found', $id));
        }

        if (Proposition::READY !== $proposition->getStatus()) {
            throw new \LogicException('A proposition that is not ready can not be approved');
        }

        if (!$this->securityContext->isGranted(Attributes::OWN, $proposition->getProduct())) {
            throw new AccessDeniedHttpException();
        }

        try {
            $this->manager->approve($proposition);
            $this->addFlash('success', 'flash.product_draft.approve.success');
        } catch (ValidatorException $e) {
            $this->addFlash('error', 'flash.product_draft.approve.error', ['%error%' => $e->getMessage()]);
        }

        return $this->redirect(
            $this->generateUrl(
                'pim_enrich_product_edit',
                [
                    'id' => $proposition->getProduct()->getId(),
                    'dataLocale' => $this->getCurrentLocaleCode()
                ]
            )
        );
    }

    /**
     * @param integer|string $id
     *
     * @return RedirectResponse
     * @throws NotFoundHttpException
     * @throws AccessDeniedHttpException
     */
    public function refuseAction($id)
    {
        if (null === $proposition = $this->repository->find($id)) {
            throw new NotFoundHttpException(sprintf('Proposition "%s" not found', $id));
        }

        if (!$this->securityContext->isGranted(Attributes::OWN, $proposition->getProduct())) {
            throw new AccessDeniedHttpException();
        }

        $this->manager->refuse($proposition);

        return $this->redirect(
            $this->generateUrl(
                'pim_enrich_product_edit',
                [
                    'id' => $proposition->getProduct()->getId(),
                    'dataLocale' => $this->getCurrentLocaleCode()
                ]
            )
        );
    }

    /**
     * Mark a proposition as ready
     *
     * @param integer|string $id
     *
     * @return RedirectResponse
     * @throws NotFoundHttpException
     * @throws AccessDeniedHttpException
     */
    public function readyAction($id)
    {
        if (null === $proposition = $this->repository->find($id)) {
            throw new NotFoundHttpException(sprintf('Proposition "%s" not found', $id));
        }

        if (!$this->securityContext->isGranted(Attributes::OWN, $proposition)) {
            throw new AccessDeniedHttpException();
        }

        $this->manager->markAsReady($proposition);

        return $this->redirect(
            $this->generateUrl(
                'pim_enrich_product_edit',
                [
                    'id' => $proposition->getProduct()->getId(),
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
