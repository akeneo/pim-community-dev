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
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionParametersParser;
use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use Pim\Bundle\DataGridBundle\Adapter\OroToPimGridFilterAdapter;
use Pim\Bundle\EnrichBundle\Flash\Message;
use Pim\Bundle\UserBundle\Context\UserContext;
use PimEnterprise\Bundle\WorkflowBundle\Manager\ProductDraftManager;
use PimEnterprise\Component\Security\Attributes as SecurityAttributes;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Exception\ValidatorException;

/**
 * ProductDraft controller
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class ProductDraftController
{
    /** @staticvar string */
    const MASS_APPROVE_JOB_CODE = 'approve_product_draft';

    /** @staticvar string */
    const MASS_REFUSE_JOB_CODE = 'refuse_product_draft';

    /** @var Request */
    protected $request;

    /** @var RouterInterface */
    protected $router;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var ObjectRepository */
    protected $repository;

    /** @var ProductDraftManager */
    protected $manager;

    /** @var UserContext */
    protected $userContext;

    /** @var JobLauncherInterface */
    protected $simpleJobLauncher;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $jobInstanceRepository;

    /** @var MassActionParametersParser */
    protected $gridParameterParser;

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /** @var OroToPimGridFilterAdapter  */
    protected $gridFilterAdapter;

    /** @var CollectionFilterInterface */
    protected $collectionFilter;

    /**
     * @param Request                               $request
     * @param RouterInterface                       $router
     * @param TokenStorageInterface                 $tokenStorage
     * @param TranslatorInterface                   $translator
     * @param ObjectRepository                      $repository
     * @param ProductDraftManager                   $manager
     * @param UserContext                           $userContext
     * @param JobLauncherInterface                  $simpleJobLauncher
     * @param IdentifiableObjectRepositoryInterface $jobInstanceRepository
     * @param MassActionParametersParser            $gridParameterParser
     * @param AuthorizationCheckerInterface         $authorizationChecker
     * @param OroToPimGridFilterAdapter             $gridFilterAdapter
     * @param CollectionFilterInterface             $collectionFilter
     */
    public function __construct(
        Request $request,
        RouterInterface $router,
        TokenStorageInterface $tokenStorage,
        TranslatorInterface $translator,
        ObjectRepository $repository,
        ProductDraftManager $manager,
        UserContext $userContext,
        JobLauncherInterface $simpleJobLauncher,
        IdentifiableObjectRepositoryInterface $jobInstanceRepository,
        MassActionParametersParser $gridParameterParser,
        AuthorizationCheckerInterface $authorizationChecker,
        OroToPimGridFilterAdapter $gridFilterAdapter,
        CollectionFilterInterface $collectionFilter
    ) {
        $this->request = $request;
        $this->router = $router;
        $this->tokenStorage = $tokenStorage;
        $this->translator = $translator;
        $this->repository = $repository;
        $this->manager = $manager;
        $this->userContext = $userContext;
        $this->simpleJobLauncher = $simpleJobLauncher;
        $this->jobInstanceRepository = $jobInstanceRepository;
        $this->gridParameterParser = $gridParameterParser;
        $this->authorizationChecker = $authorizationChecker;
        $this->gridFilterAdapter = $gridFilterAdapter;
        $this->collectionFilter = $collectionFilter;
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
        if (!$this->authorizationChecker->isGranted(SecurityAttributes::OWN_AT_LEAST_ONE_CATEGORY)) {
            throw new AccessDeniedException();
        }

        return [];
    }

    /**
     * @param Request    $request
     * @param int|string $id
     * @param string     $action  either "approve" or "refuse"
     *
     * @throws \LogicException
     * @throws NotFoundHttpException
     * @throws AccessDeniedHttpException
     *
     * @return JsonResponse|RedirectResponse
     */
    public function reviewAction(Request $request, $id, $action)
    {
        if (null === $productDraft = $this->repository->find($id)) {
            throw new NotFoundHttpException(sprintf('Product draft "%s" not found', $id));
        }

        if (!$this->authorizationChecker->isGranted(SecurityAttributes::OWN, $productDraft->getProduct())) {
            throw new AccessDeniedHttpException();
        }

        try {
            $this->manager->$action($productDraft, ['comment' => $request->request->get('comment')]);
            $status = 'success';
            $messageParams = [];
        } catch (ValidatorException $e) {
            $status = 'error';
            $messageParams = ['%error%' => $e->getMessage()];
        }

        $message = 'approve' === $action ?
            $this->translator->trans(sprintf('flash.product_draft.approve.%s', $status), $messageParams) :
            $this->translator->trans('flash.product_draft.refuse.success');

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'successful' => $status === 'success',
                    'message'    => $message
                ]
            );
        }

        $this->request->getSession()->getFlashBag()
            ->add($status, new Message($message));

        return new RedirectResponse(
            $this->router->generate(
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
     * @return RedirectResponse
     */
    public function massApproveAction(Request $request)
    {
        $request->request->add(['actionName' => 'massApprove' ]);
        $params = $this->gridFilterAdapter->adapt($request);
        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier(self::MASS_APPROVE_JOB_CODE);
        $configuration = [
            'draftIds' => $params['values'],
            'comment'  => $request->get('comment'),
        ];

        $jobExecution = $this->simpleJobLauncher
            ->launch($jobInstance, $this->tokenStorage->getToken()->getUser(), $configuration);

        return new JsonResponse(['jobExecutionId' => $jobExecution->getId()]);
    }

    /**
     * Launch the mass refuse job
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function massRefuseAction(Request $request)
    {
        $request->request->add(['actionName' => 'massApprove' ]);
        $params = $this->gridFilterAdapter->adapt($request);
        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier(self::MASS_REFUSE_JOB_CODE);
        $configuration = [
            'draftIds' => $params['values'],
            'comment'  => $request->get('comment'),
        ];

        $jobExecution = $this->simpleJobLauncher
            ->launch($jobInstance, $this->tokenStorage->getToken()->getUser(), $configuration);

        return new JsonResponse(['jobExecutionId' => $jobExecution->getId()]);
    }

    /**
     * Redirects to the process tracker for the following job execution id.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function massActionRedirectAction(Request $request)
    {
        $jobExecutionId = $request->get('jobExecutionId');

        return new RedirectResponse(
            $this->router->generate('pim_enrich_job_tracker_show', ['id' => $jobExecutionId])
        );
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
