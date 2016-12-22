<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ActivityManagerBundle\Controller;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PimEnterprise\Component\ActivityManager\Repository\ProjectCompletenessRepositoryInterface;
use PimEnterprise\Bundle\ActivityManagerBundle\Security\ProjectVoter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class ProjectCompletenessController
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $projectRepository;

    /** @var ProjectCompletenessRepositoryInterface */
    protected $projectCompletenessRepository;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /**
     * ProjectCompletenessController constructor.
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $projectRepository,
        ProjectCompletenessRepositoryInterface $projectCompletenessRepository,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->projectRepository = $projectRepository;
        $this->projectCompletenessRepository = $projectCompletenessRepository;
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @param int     $projectCode
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function showAction($projectCode, Request $request)
    {
        $project = $this->projectRepository->findOneByIdentifier($projectCode);

        if (null === $project) {
            return new JsonResponse(null, 404);
        }

        if (!$this->authorizationChecker->isGranted([ProjectVoter::OWN, ProjectVoter::CONTRIBUTE], $project)) {
            throw new AccessDeniedException();
        }

        $contributor = null;
        if ($this->authorizationChecker->isGranted(ProjectVoter::CONTRIBUTE, $project)) {
            $contributor = $this->tokenStorage->getToken()->getUser()->getUsername();
        }

        if ($this->authorizationChecker->isGranted(ProjectVoter::OWN, $project)) {
            $contributor = $request->get('contributor');
        }

        $projectCompleteness = $this->projectCompletenessRepository->getProjectCompleteness($project, $contributor);

        return new JsonResponse($projectCompleteness);
    }
}
