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
use PimEnterprise\Bundle\ActivityManagerBundle\Security\ProjectVoter;
use PimEnterprise\Component\ActivityManager\Repository\ProjectCompletenessRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

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

    /** @var NormalizerInterface */
    protected $completenessNormalizer;

    /**
     * @param IdentifiableObjectRepositoryInterface  $projectRepository
     * @param ProjectCompletenessRepositoryInterface $projectCompletenessRepository
     * @param TokenStorageInterface                  $tokenStorage
     * @param AuthorizationCheckerInterface          $authorizationChecker
     * @param NormalizerInterface                    $completenessNormalizer
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $projectRepository,
        ProjectCompletenessRepositoryInterface $projectCompletenessRepository,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker,
        NormalizerInterface $completenessNormalizer
    ) {
        $this->projectRepository = $projectRepository;
        $this->projectCompletenessRepository = $projectCompletenessRepository;
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
        $this->completenessNormalizer = $completenessNormalizer;
    }

    /**
     * @param int     $identifier
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function showAction($identifier, Request $request)
    {
        $project = $this->projectRepository->findOneByIdentifier($identifier);

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
        $projectCompleteness = $this->completenessNormalizer->normalize($projectCompleteness, 'internal_api');

        return new JsonResponse($projectCompleteness);
    }
}
