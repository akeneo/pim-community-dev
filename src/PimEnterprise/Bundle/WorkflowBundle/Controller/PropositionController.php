<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Controller;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Doctrine\Common\Persistence\ObjectRepository;
use Pim\Bundle\EnrichBundle\AbstractController\AbstractController;
use PimEnterprise\Bundle\WorkflowBundle\Manager\PropositionManager;
use PimEnterprise\Bundle\SecurityBundle\Attributes;

/**
 * Proposition controller
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class PropositionController extends AbstractController
{
    /** @var ObjectRepository */
    protected $repository;

    /** @var PropositionManager */
    protected $manager;

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
     * @param PropositionManager       $manager
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
        PropositionManager $manager
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
        $this->repository = $repository;
        $this->manager    = $manager;
    }

    /**
     * @param integer|string $id
     *
     * @return RedirectResponse
     * @throws NotFoundHttpException
     */
    public function approveAction($id)
    {
        if (null === $proposition = $this->repository->find($id)) {
            throw new NotFoundHttpException(sprintf('Proposition "%s" not found', $id));
        }

        if (!$this->securityContext->isGranted(Attributes::OWNER, $proposition->getProduct())) {
            throw new AccessDeniedHttpException();
        }

        $this->manager->approve($proposition);

        return $this->redirect(
            $this->generateUrl(
                'pim_enrich_product_edit',
                [
                    'id' => $proposition->getProduct()->getId()
                ]
            )
        );
    }

    /**
     * @param integer|string $id
     *
     * @return RedirectResponse
     * @throws NotFoundHttpException
     */
    public function refuseAction($id)
    {
        if (null === $proposition = $this->repository->find($id)) {
            throw new NotFoundHttpException(sprintf('Proposition "%s" not found', $id));
        }

        if (!$this->securityContext->isGranted(Attributes::OWNER, $proposition->getProduct())) {
            throw new AccessDeniedHttpException();
        }

        $this->manager->refuse($proposition);

        return $this->redirect(
            $this->generateUrl(
                'pim_enrich_product_edit',
                [
                    'id' => $proposition->getProduct()->getId()
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
    public function readyAction($id)
    {
        if (null === $proposition = $this->repository->find($id)) {
            throw new NotFoundHttpException(sprintf('Proposition "%s" not found', $id));
        }

        if (!$this->securityContext->isGranted(Attributes::OWNER, $proposition)) {
            throw new AccessDeniedHttpException();
        }

        $this->manager->markAsReady($proposition);

        return $this->redirect(
            $this->generateUrl(
                'pim_enrich_product_edit',
                [
                    'id' => $proposition->getProduct()->getId()
                ]
            )
        );
    }
}
