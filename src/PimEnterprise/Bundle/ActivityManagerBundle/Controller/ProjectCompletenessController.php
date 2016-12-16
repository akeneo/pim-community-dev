<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ActivityManager\Bundle\Controller;

use Akeneo\ActivityManager\Component\Repository\ProjectCompletenessRepositoryInterface;
use Akeneo\ActivityManager\Component\Voter\ProjectVoter;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class ProjectCompletenessController
{
    /** @var IdentifiableObjectRepositoryInterface */
    private $projectRepository;

    /** @var ProjectCompletenessRepositoryInterface */
    private $projectCompletenessRepository;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /**
     * ProjectCompletenessController constructor.
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $projectRepository,
        ProjectCompletenessRepositoryInterface $projectCompletenessRepository,
        TokenStorageInterface $tokenStorage
    ) {
        $this->projectRepository = $projectRepository;
        $this->projectCompletenessRepository = $projectCompletenessRepository;
        $this->tokenStorage = $tokenStorage;
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

        $this->denyAccessUnlessGranted([ProjectVoter::OWN, ProjectVoter::CONTRIBUTE], $project);

        $contributor = null;

        if ($this->isGranted(ProjectVoter::CONTRIBUTE, $project)) {
            $contributor = $this->tokenStorage->getToken()->getUser()->getUsername();
        }

        if ($this->isGranted(ProjectVoter::OWN, $project)) {
            $contributor = $request->get('contributor');
        }

        $projectCompleteness = $this->projectCompletenessRepository->getProjectCompleteness($project, $contributor);

        return new JsonResponse($projectCompleteness);
    }
}
