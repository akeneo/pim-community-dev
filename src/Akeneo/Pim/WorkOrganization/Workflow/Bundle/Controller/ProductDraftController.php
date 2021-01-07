<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Controller;

use Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Manager\EntityWithValuesDraftManager;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Doctrine\Common\Persistence\ObjectRepository;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionParametersParser;
use Oro\Bundle\PimDataGridBundle\Adapter\OroToPimGridFilterAdapter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

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

    /** @var ObjectRepository */
    protected $repository;

    /** @var EntityWithValuesDraftManager */
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

    public function __construct(
        RequestStack $requestStack,
        RouterInterface $router,
        TokenStorageInterface $tokenStorage,
        ObjectRepository $repository,
        EntityWithValuesDraftManager $manager,
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
            'productDraftIds' => $filters['values']['product_draft_ids'],
            'productModelDraftIds' => $filters['values']['product_model_draft_ids'],
            'comment'  => $request->get('comment'),
            'user_to_notify' => $user->getUsername()
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
            'productDraftIds' => $filters['values']['product_draft_ids'],
            'productModelDraftIds' => $filters['values']['product_model_draft_ids'],
            'comment'  => $request->get('comment'),
            'user_to_notify' => $user->getUsername(),
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
