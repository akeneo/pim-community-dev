<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Controller;

use Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface;
use Akeneo\Pim\Permission\Bundle\User\UserContext;
use Akeneo\Pim\Permission\Component\Attributes as SecurityAttributes;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Manager\EntityWithValuesDraftManager;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionParametersParser;
use Oro\Bundle\PimDataGridBundle\Adapter\OroToPimGridFilterAdapter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Product model draft controller
 *
 * @author Philippe Mossière <philippe.mossiere@akeneo.com>
 */
class ProductModelDraftController
{
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

    /** @var OroToPimGridFilterAdapter */
    protected $gridFilterAdapter;

    /** @var CollectionFilterInterface */
    protected $collectionFilter;

    /** @var MassActionParametersParser */
    protected $parameterParser;

    public function __construct(
        RequestStack $requestStack,
        RouterInterface $router,
        TokenStorageInterface $tokenStorage,
        TranslatorInterface $translator,
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

    public function reviewAction(Request $request, $id, $action): JsonResponse
    {
        if (null === $productDraft = $this->repository->find($id)) {
            throw new NotFoundHttpException(sprintf('Product draft "%s" not found', $id));
        }

        if (!$this->authorizationChecker->isGranted(SecurityAttributes::OWN, $productDraft->getEntityWithValue())) {
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
                'message'    => $message,
            ]
        );
    }
}
