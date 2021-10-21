<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Controller;

use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Security\ProjectVoter;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Repository\ProjectCompletenessRepositoryInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Repository\ProjectRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class ProjectCompletenessController
{
    protected ProjectRepositoryInterface $projectRepository;
    protected ProjectCompletenessRepositoryInterface $projectCompletenessRepository;
    protected AuthorizationCheckerInterface $authorizationChecker;
    protected NormalizerInterface $projectCompletenessNormalizer;

    public function __construct(
        ProjectRepositoryInterface $projectRepository,
        ProjectCompletenessRepositoryInterface $projectCompletenessRepository,
        AuthorizationCheckerInterface $authorizationChecker,
        NormalizerInterface $projectCompletenessNormalizer
    ) {
        $this->projectRepository = $projectRepository;
        $this->projectCompletenessRepository = $projectCompletenessRepository;
        $this->authorizationChecker = $authorizationChecker;
        $this->projectCompletenessNormalizer = $projectCompletenessNormalizer;
    }

    public function showAction(string $identifier, Request $request): JsonResponse
    {
        $project = $this->projectRepository->findOneByIdentifier($identifier);

        if (null === $project) {
            return new JsonResponse(null, 404);
        }

        if (!$this->authorizationChecker->isGranted(ProjectVoter::OWN, $project)
            && !$this->authorizationChecker->isGranted(ProjectVoter::CONTRIBUTE, $project)) {
            throw new AccessDeniedException();
        }

        $contributor = $request->get('contributor');

        $projectCompleteness = $this->projectCompletenessRepository->getProjectCompleteness($project, $contributor);
        $projectCompleteness = $this->projectCompletenessNormalizer->normalize($projectCompleteness, 'internal_api');

        $projectCompleteness['is_completeness_computed'] = $project->isCompletenessComputed();

        return new JsonResponse($projectCompleteness);
    }
}
