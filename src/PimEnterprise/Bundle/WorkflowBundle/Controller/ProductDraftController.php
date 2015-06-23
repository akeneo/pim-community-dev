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

use Akeneo\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionParametersParser;
use Pim\Bundle\EnrichBundle\AbstractController\AbstractController;
use Pim\Bundle\UserBundle\Context\UserContext;
use PimEnterprise\Bundle\ImportExportBundle\Entity\Repository\JobInstanceRepository;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\WorkflowBundle\Manager\ProductDraftManager;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraft;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
    /** @staticvar string */
    const MASS_APPROVE_JOB_CODE = 'approve_product_draft';

    /** @staticvar string */
    const MASS_REFUSE_JOB_CODE  = 'refuse_product_draft';

    /** @var ObjectRepository */
    protected $repository;

    /** @var ProductDraftManager */
    protected $manager;

    /** @var UserContext */
    protected $userContext;

    /** @var JobLauncherInterface */
    protected $simpleJobLauncher;

    /** @var JobInstanceRepository */
    protected $jobInstanceRepository;

    /** @var MassActionParametersParser */
    protected $gridParameterParser;

    /**
     * @param Request                        $request
     * @param EngineInterface                $templating
     * @param RouterInterface                $router
     * @param SecurityContextInterface       $securityContext
     * @param FormFactoryInterface           $formFactory
     * @param ValidatorInterface             $validator
     * @param TranslatorInterface            $translator
     * @param EventDispatcherInterface       $eventDispatcher
     * @param ObjectRepository               $repository
     * @param ProductDraftManager            $manager
     * @param UserContext                    $userContext
     * @param JobLauncherInterface           $simpleJobLauncher
     * @param JobInstanceRepository          $jobInstanceRepository
     * @param MassActionParametersParser     $gridParameterParser
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
        UserContext $userContext,
        JobLauncherInterface $simpleJobLauncher,
        JobInstanceRepository $jobInstanceRepository,
        MassActionParametersParser $gridParameterParser
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
        $this->repository            = $repository;
        $this->manager               = $manager;
        $this->userContext           = $userContext;
        $this->simpleJobLauncher     = $simpleJobLauncher;
        $this->jobInstanceRepository = $jobInstanceRepository;
        $this->gridParameterParser   = $gridParameterParser;
    }

    /**
     * List proposals
     *
     * @Template
     *
     * @throws AccessDeniedException if the current user is not the owner of any categories
     *
     * @return Response
     */
    public function indexAction()
    {
        if (!$this->securityContext->isGranted(Attributes::OWN_AT_LEAST_ONE_CATEGORY)) {
            throw new AccessDeniedException();
        }

        return [];
    }

    /**
     * @param Request    $request
     * @param int|string $id
     *
     * @throws \LogicException
     * @throws NotFoundHttpException
     * @throws AccessDeniedHttpException
     *
     * @return JsonResponse|RedirectResponse
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
                    'message'    => $this->getTranslator()->trans(
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
                    'id'         => $productDraft->getProduct()->getId(),
                    'dataLocale' => $this->getCurrentLocaleCode()
                ]
            )
        );
    }

    /**
     * @param Request    $request
     * @param int|string $id
     *
     * @throws NotFoundHttpException
     * @throws AccessDeniedHttpException
     *
     * @return RedirectResponse
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
                    'message'    => $this->getTranslator()->trans('flash.product_draft.refuse.success')
                ]
            );
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
     * Mark a product draft as ready
     *
     * @param int|string $id
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
     * Launch the mass approve job
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function massApproveAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException();
        }

        $this->launchMassReviewJob(
            self::MASS_APPROVE_JOB_CODE,
            $this->parseGridParameters($request)
        );

        return new JsonResponse(
            [
                'successful' => true,
                'message'    => $this->getTranslator()->trans('flash.product_draft.mass_approve.launched')
            ]
        );
    }

    /**
     * Launch the mass refuse job
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function massRefuseAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException();
        }

        $this->launchMassReviewJob(
            self::MASS_REFUSE_JOB_CODE,
            $this->parseGridParameters($request)
        );

        return new JsonResponse(
            [
                'successful' => true,
                'message'    => $this->getTranslator()->trans('flash.product_draft.mass_refuse.launched')
            ]
        );
    }

    /**
     * Launch the specified mass review job
     *
     * @param string  $jobCode
     * @param array   $params
     */
    protected function launchMassReviewJob($jobCode, array $params)
    {
        $jobInstance      = $this->jobInstanceRepository->findOneByIdentifier($jobCode);
        $rawConfiguration = addslashes(json_encode(['draftIds' => $params['values']]));

        $this->simpleJobLauncher->launch($jobInstance, $this->getUser(), $rawConfiguration);
    }

    /**
     * Transform the query string build by the Oro grid into a usable array
     *
     * @param Request $request
     *
     * @return array
     */
    protected function parseGridParameters(Request $request)
    {
        return $this->gridParameterParser->parse($request);
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
