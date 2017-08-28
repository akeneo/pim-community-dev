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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
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

    /** @var RequestStack */
    protected $requestStack;

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

    /** @var MassActionParametersParser */
    protected $parameterParser;

    /**
     * @param RequestStack                          $requestStack
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
     * @param MassActionParametersParser            $parameterParser
     */
    public function __construct(
        RequestStack $requestStack,
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
        CollectionFilterInterface $collectionFilter,
        MassActionParametersParser $parameterParser
    ) {
        $this->requestStack = $requestStack;
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
        $this->parameterParser = $parameterParser;
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
     * @return JsonResponse
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

        return new JsonResponse(
            [
                'successful' => $status === 'success',
                'message'    => $message
            ]
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
        $request->request->add(['actionName' => 'massApprove']);
        $parameters = $this->parameterParser->parse($request);
        $filters = $this->gridFilterAdapter->adapt($parameters);
        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier(self::MASS_APPROVE_JOB_CODE);
        $user = $this->tokenStorage->getToken()->getUser();

        $configuration = [
            'draftIds' => $filters['values'],
            'comment'  => $request->get('comment'),
            'notification_user' => $user->getUsername()
        ];

        $jobExecution = $this->simpleJobLauncher->launch($jobInstance, $user, $configuration);

        return new JsonResponse(
            [
                'route'  => 'pim_enrich_job_tracker_show',
                'params' => ['id' => $jobExecution->getId()],
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
        $request->request->add(['actionName' => 'massApprove']);
        $parameters = $this->parameterParser->parse($request);
        $filters = $this->gridFilterAdapter->adapt($parameters);
        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier(self::MASS_REFUSE_JOB_CODE);
        $user = $this->tokenStorage->getToken()->getUser();

        $configuration = [
            'draftIds' => $filters['values'],
            'comment'  => $request->get('comment'),
            'notification_user' => $user->getUsername(),
        ];

        $jobExecution = $this->simpleJobLauncher->launch($jobInstance, $user, $configuration);

        return new JsonResponse(
            [
                'route'  => 'pim_enrich_job_tracker_show',
                'params' => ['id' => $jobExecution->getId()],
            ]
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
