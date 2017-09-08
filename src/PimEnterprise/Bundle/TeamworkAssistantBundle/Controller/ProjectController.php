<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\TeamworkAssistantBundle\Controller;

use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Repository\SearchableRepositoryInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PimEnterprise\Bundle\FilterBundle\Filter\Product\ProjectCompletenessFilter;
use PimEnterprise\Bundle\TeamworkAssistantBundle\Datagrid\FilterConverter;
use PimEnterprise\Bundle\TeamworkAssistantBundle\Job\ProjectCalculationJobLauncher;
use PimEnterprise\Bundle\TeamworkAssistantBundle\Security\ProjectVoter;
use PimEnterprise\Component\TeamworkAssistant\Factory\ProjectFactoryInterface;
use PimEnterprise\Component\TeamworkAssistant\Repository\ProjectCompletenessRepositoryInterface;
use PimEnterprise\Component\TeamworkAssistant\Repository\ProjectRepositoryInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Project controller.
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class ProjectController
{
    /** @var FilterConverter */
    protected $filterConverter;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var ProjectFactoryInterface */
    protected $projectFactory;

    /** @var SaverInterface */
    protected $projectSaver;

    /** @var ObjectUpdaterInterface */
    protected $projectUpdater;

    /** @var RemoverInterface */
    protected $projectRemover;

    /** @var ProjectCalculationJobLauncher*/
    protected $projectCalculationJobLauncher;

    /** @var NormalizerInterface */
    protected $projectNormalizer;

    /** @var ProjectRepositoryInterface */
    protected $projectRepository;

    /** @var SearchableRepositoryInterface */
    protected $userRepository;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var ProjectCompletenessRepositoryInterface */
    protected $projectCompletenessRepository;

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /** @var RouterInterface */
    protected $router;

    /** @var NormalizerInterface */
    protected $projectCompletenessNormalizer;

    /**
     * @param FilterConverter                        $filterConverter
     * @param ProjectFactoryInterface                $projectFactory
     * @param SaverInterface                         $projectSaver
     * @param ObjectUpdaterInterface                 $projectUpdater
     * @param RemoverInterface                       $projectRemover
     * @param ValidatorInterface                     $validator
     * @param ProjectCalculationJobLauncher          $projectCalculationJobLauncher
     * @param NormalizerInterface                    $projectNormalizer
     * @param ProjectRepositoryInterface             $projectRepository
     * @param SearchableRepositoryInterface          $userRepository
     * @param TokenStorageInterface                  $tokenStorage
     * @param ProjectCompletenessRepositoryInterface $projectCompletenessRepository
     * @param AuthorizationCheckerInterface          $authorizationChecker
     * @param RouterInterface                        $router
     * @param NormalizerInterface                    $projectCompletenessNormalizer
     */
    public function __construct(
        FilterConverter $filterConverter,
        ProjectFactoryInterface $projectFactory,
        SaverInterface $projectSaver,
        ObjectUpdaterInterface $projectUpdater,
        RemoverInterface $projectRemover,
        ValidatorInterface $validator,
        ProjectCalculationJobLauncher $projectCalculationJobLauncher,
        NormalizerInterface $projectNormalizer,
        ProjectRepositoryInterface $projectRepository,
        SearchableRepositoryInterface $userRepository,
        TokenStorageInterface $tokenStorage,
        ProjectCompletenessRepositoryInterface $projectCompletenessRepository,
        AuthorizationCheckerInterface $authorizationChecker,
        RouterInterface $router,
        NormalizerInterface $projectCompletenessNormalizer
    ) {
        $this->filterConverter = $filterConverter;
        $this->projectFactory = $projectFactory;
        $this->validator = $validator;
        $this->projectSaver = $projectSaver;
        $this->projectUpdater = $projectUpdater;
        $this->projectCalculationJobLauncher = $projectCalculationJobLauncher;
        $this->projectNormalizer = $projectNormalizer;
        $this->projectRepository = $projectRepository;
        $this->userRepository = $userRepository;
        $this->tokenStorage = $tokenStorage;
        $this->projectCompletenessRepository = $projectCompletenessRepository;
        $this->authorizationChecker = $authorizationChecker;
        $this->router = $router;
        $this->projectCompletenessNormalizer = $projectCompletenessNormalizer;
        $this->projectRemover = $projectRemover;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function createAction(Request $request)
    {
        $datagridViewFilters = [];
        $projectData = $request->request->get('project');
        $user = $this->tokenStorage->getToken()->getUser();

        parse_str($projectData['datagrid_view']['filters'], $datagridViewFilters);

        if (isset($projectData['code'])) {
            $project = $this->projectRepository->findOneByIdentifier($projectData['code']);

            if (null === $project || !$this->authorizationChecker->isGranted([ProjectVoter::OWN], $project)) {
                return new JsonResponse(sprintf('No project with code "%s"', $projectData['code']), 400);
            }

            $projectData = array_intersect_key($projectData, array_flip(['label', 'due_date', 'description']));
            $this->projectUpdater->update($project, $projectData);
        } else {
            $projectData['owner'] = $user->getUsername();
            $projectData['channel'] = $datagridViewFilters['f']['scope']['value'];
            $projectData['product_filters'] = $this->filterConverter->convert(
                $request,
                json_encode($datagridViewFilters['f'])
            );

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

    /**
     * @param string $identifier
     *
     * @return JsonResponse
     */
    public function removeAction($identifier)
    {
        $project = $this->projectRepository->findOneByIdentifier($identifier);

        if (null === $project || !$this->authorizationChecker->isGranted([ProjectVoter::OWN], $project)) {
            return new JsonResponse(sprintf('No project with code "%s"', $identifier), 400);
        }

        $this->projectRemover->remove($project);

        return new JsonResponse(null, 204);
    }

    /**
     * Returns Projects in terms of search and options.
     * Options accept 'limit' => (int) and 'page' => (int) and 'user' => UserInterface.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function searchAction(Request $request)
    {
        $options = ['limit' => 20, 'page' => 1, 'completeness' => '1'];
        $options = array_merge($options, $request->query->get('options', []));
        $contributor = $this->tokenStorage->getToken()->getUser()->getUsername();
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

    /**
     * @param string $identifier
     *
     * @return JsonResponse
     */
    public function getAction($identifier)
    {
        $contributor = $this->tokenStorage->getToken()->getUser()->getUsername();
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

    /**
     * Returns users that belong to the project.
     *
     * @param Request $request
     * @param string  $identifier
     *
     * @return JsonResponse
     */
    public function searchContributorsAction($identifier, Request $request)
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
     *
     * @param string $identifier
     * @param string $status
     *
     * @Template("PimEnterpriseTeamworkAssistantBundle:Project:filter-grid.html.twig")
     *
     * @return array|RedirectResponse|JsonResponse
     */
    public function showAction($identifier, $status)
    {
        $project = $this->projectRepository->findOneByIdentifier($identifier);

        if (null === $project ||
            !$this->authorizationChecker->isGranted([ProjectVoter::OWN, ProjectVoter::CONTRIBUTE], $project)
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
            if (!$this->authorizationChecker->isGranted([ProjectVoter::OWN], $project)) {
                return new JsonResponse([
                    'route' => 'pim_enrich_product_index'
                ]);
            }

            $statusCode = $ownerStatuses[$status];
        } elseif (array_key_exists($status, $contributorStatuses)) {
            $statusCode = $contributorStatuses[$status];
        }

        return [
            'view'   => $project->getDatagridView(),
            'status' => $statusCode
        ];
    }
}
