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

use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Datagrid\Filter\ProjectCompletenessFilter;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Datagrid\FilterConverter;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Job\ProjectCalculationJobLauncher;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Security\ProjectVoter;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Factory\ProjectFactoryInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Repository\ProjectCompletenessRepositoryInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Repository\ProjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\SearchableRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Twig\Environment;

/**
 * Project controller.
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class ProjectController
{
    public function __construct(
        private readonly FilterConverter $filterConverter,
        private readonly ProjectFactoryInterface $projectFactory,
        private readonly SaverInterface $projectSaver,
        private readonly ObjectUpdaterInterface $projectUpdater,
        private readonly RemoverInterface $projectRemover,
        private readonly ValidatorInterface $validator,
        private readonly ProjectCalculationJobLauncher $projectCalculationJobLauncher,
        private readonly NormalizerInterface $projectNormalizer,
        private readonly ProjectRepositoryInterface $projectRepository,
        private readonly SearchableRepositoryInterface $userRepository,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly ProjectCompletenessRepositoryInterface $projectCompletenessRepository,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly NormalizerInterface $projectCompletenessNormalizer,
        private readonly Environment $twig,
    ) {
    }

    public function createAction(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $datagridViewFilters = [];
        $projectData = $request->request->get('project');
        $user = $this->tokenStorage->getToken()->getUser();

        parse_str($projectData['datagrid_view']['filters'], $datagridViewFilters);

        if (isset($projectData['code'])) {
            $project = $this->projectRepository->findOneByIdentifier($projectData['code']);

            if (null === $project || !$this->authorizationChecker->isGranted(ProjectVoter::OWN, $project)) {
                return new JsonResponse(sprintf('No project with code "%s"', $projectData['code']), 400);
            }

            $projectData = array_intersect_key($projectData, array_flip(['label', 'due_date', 'description']));
            $this->projectUpdater->update($project, $projectData);
        } else {
            $projectData['owner'] = $user->getUserIdentifier();
            $projectData['product_filters'] = $this->filterConverter->convert($datagridViewFilters['f']);

            $project = $this->projectFactory->create($projectData);
        }

        $violations = $this->validator->validate($project);

        if (0 === $violations->count()) {
            $this->projectSaver->save($project);
            $this->projectCalculationJobLauncher->launch($project);

            $normalizedProject = $this->projectNormalizer->normalize($project, 'internal_api');

            return new JsonResponse($normalizedProject, 201);
        }

        $errors = [];
        foreach ($violations as $violation) {
            $errors[] = [
                'field'   => $violation->getPropertyPath(),
                'message' => $violation->getMessage(),
            ];
        }

        return new JsonResponse($errors, 400);
    }

    public function removeAction(Request $request, string $identifier): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $project = $this->projectRepository->findOneByIdentifier($identifier);

        if (null === $project || !$this->authorizationChecker->isGranted(ProjectVoter::OWN, $project)) {
            return new JsonResponse(sprintf('No project with code "%s"', $identifier), 400);
        }

        $this->projectRemover->remove($project);

        return new JsonResponse(null, 204);
    }

    /**
     * Returns Projects in terms of search and options.
     * Options accept 'limit' => (int) and 'page' => (int) and 'user' => UserInterface.
     */
    public function searchAction(Request $request): JsonResponse
    {
        $options = ['limit' => 20, 'page' => 1, 'completeness' => '1'];
        $options = array_merge($options, $request->query->get('options', []));
        $contributor = $this->tokenStorage->getToken()->getUser()->getUserIdentifier();
        $computeCompleteness = boolval($options['completeness']);

        $projects = $this->projectRepository->findBySearch(
            $request->query->get('search'),
            [
                'limit' => $options['limit'],
                'page'  => $options['page'],
                'user'  => $this->tokenStorage->getToken()->getUser(),
            ]
        );

        $normalizedProjects = [];
        foreach ($projects as $project) {
            $normalizedProject = $this->projectNormalizer->normalize($project, 'internal_api');

            /* For scalability reasons, the completeness is not attached to the normalizer. Indeed, the computing of a
             * completeness can be very time consuming. */
            if ($computeCompleteness) {
                $normalizedProject['completeness'] = $this->projectCompletenessNormalizer->normalize(
                    $this->projectCompletenessRepository->getProjectCompleteness(
                        $project,
                        $contributor
                    ),
                    'internal_api'
                );
            }

            $normalizedProjects[] = $normalizedProject;
        }

        return new JsonResponse($normalizedProjects, 200);
    }

    public function getAction(string $identifier): JsonResponse
    {
        $contributor = $this->tokenStorage->getToken()->getUser()->getUserIdentifier();
        $project = $this->projectRepository->findOneByIdentifier($identifier);

        $normalizedProject = $this->projectNormalizer->normalize($project, 'internal_api');

        /* For scalability reasons, the completeness is not attached to the normalizer. Indeed, the computing of a
         * completeness can be very time consuming. */
        $normalizedProject['completeness'] = $this->projectCompletenessNormalizer->normalize(
            $this->projectCompletenessRepository->getProjectCompleteness(
                $project,
                $contributor
            ),
            'internal_api'
        );

        return new JsonResponse($normalizedProject, 200);
    }

    public function searchContributorsAction(string $identifier, Request $request): JsonResponse
    {
        $project = $this->projectRepository->findOneByIdentifier($identifier);

        if (null === $project) {
            return new JsonResponse(null, 404);
        }

        $options = $request->query->get('options', ['limit' => 20, 'page' => 1]);

        $users = $this->userRepository->findBySearch(
            $request->query->get('search'),
            [
                'limit'   => $options['limit'],
                'page'    => $options['page'],
                'project' => $project,
            ]
        );

        $normalizedProjects = $this->projectNormalizer->normalize($users, 'internal_api');

        return new JsonResponse($normalizedProjects, 200);
    }

    /**
     * The "show" action of a project means redirecting the user on the datagrid filtered with the Project's view.
     * If a "status" is specified, we fill in the project completeness filter depending on the user permissions.
     */
    public function showAction(string $identifier, string $status): Response
    {
        $project = $this->projectRepository->findOneByIdentifier($identifier);

        if (null === $project ||
            !($this->authorizationChecker->isGranted(ProjectVoter::OWN, $project) || $this->authorizationChecker->isGranted(ProjectVoter::CONTRIBUTE, $project))
        ) {
            return new JsonResponse([
                'route' => 'pim_enrich_product_index'
            ]);
        }

        $ownerStatuses = [
            'owner-todo'       => ProjectCompletenessFilter::OWNER_TODO,
            'owner-inprogress' => ProjectCompletenessFilter::OWNER_IN_PROGRESS,
            'owner-done'       => ProjectCompletenessFilter::OWNER_DONE,
        ];

        $contributorStatuses = [
            'contributor-todo'       => ProjectCompletenessFilter::CONTRIBUTOR_TODO,
            'contributor-inprogress' => ProjectCompletenessFilter::CONTRIBUTOR_IN_PROGRESS,
            'contributor-done'       => ProjectCompletenessFilter::CONTRIBUTOR_DONE
        ];

        $statusCode = 0;

        if (array_key_exists($status, $ownerStatuses)) {
            if (!$this->authorizationChecker->isGranted(ProjectVoter::OWN, $project)) {
                return new JsonResponse([
                    'route' => 'pim_enrich_product_index'
                ]);
            }

            $statusCode = $ownerStatuses[$status];
        } elseif (array_key_exists($status, $contributorStatuses)) {
            $statusCode = $contributorStatuses[$status];
        }

        $content = $this->twig->render('@AkeneoPimTeamworkAssistant/Project/filter-grid.html.twig', [
            'view'   => $project->getDatagridView(),
            'status' => $statusCode,
            'locale' => $project->getLocale()->getCode(),
        ]);

        return new Response($content);
    }
}
