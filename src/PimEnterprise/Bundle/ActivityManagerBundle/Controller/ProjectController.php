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

use Akeneo\Component\StorageUtils\Repository\SearchableRepositoryInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use PimEnterprise\Bundle\ActivityManagerBundle\Datagrid\FilterConverter;
use PimEnterprise\Bundle\ActivityManagerBundle\Job\ProjectCalculationJobLauncher;
use PimEnterprise\Bundle\ActivityManagerBundle\Security\ProjectVoter;
use PimEnterprise\Component\ActivityManager\Builder\ProjectBuilderInterface;
use PimEnterprise\Component\ActivityManager\Repository\ProjectCompletenessRepositoryInterface;
use PimEnterprise\Component\ActivityManager\Repository\ProjectRepositoryInterface;
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

    /** @var SaverInterface */
    protected $projectSaver;

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

    /**
     * @param FilterConverter                        $filterConverter
     * @param SaverInterface                         $projectSaver
     * @param ValidatorInterface                     $validator
     * @param ProjectCalculationJobLauncher          $projectCalculationJobLauncher
     * @param NormalizerInterface                    $projectNormalizer
     * @param ProjectRepositoryInterface             $projectRepository
     * @param SearchableRepositoryInterface          $userRepository
     * @param TokenStorageInterface                  $tokenStorage
     * @param ProjectCompletenessRepositoryInterface $projectCompletenessRepository
     * @param AuthorizationCheckerInterface          $authorizationChecker
     * @param RouterInterface                        $router
     */
    public function __construct(
        FilterConverter $filterConverter,
        ProjectBuilderInterface $projectBuilder,
        SaverInterface $projectSaver,
        ValidatorInterface $validator,
        ProjectCalculationJobLauncher $projectCalculationJobLauncher,
        NormalizerInterface $projectNormalizer,
        ProjectRepositoryInterface $projectRepository,
        SearchableRepositoryInterface $userRepository,
        TokenStorageInterface $tokenStorage,
        ProjectCompletenessRepositoryInterface $projectCompletenessRepository,
        AuthorizationCheckerInterface $authorizationChecker,
        RouterInterface $router
    ) {
        $this->filterConverter = $filterConverter;
        $this->projectBuilder = $projectBuilder;
        $this->validator = $validator;
        $this->projectSaver = $projectSaver;
        $this->projectCalculationJobLauncher = $projectCalculationJobLauncher;
        $this->projectNormalizer = $projectNormalizer;
        $this->projectRepository = $projectRepository;
        $this->userRepository = $userRepository;
        $this->tokenStorage = $tokenStorage;
        $this->projectCompletenessRepository = $projectCompletenessRepository;
        $this->authorizationChecker = $authorizationChecker;
        $this->router = $router;
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

        $projectData['owner'] = $user->getUsername();
        $projectData['channel'] = $datagridViewFilters['f']['scope']['value'];
        $projectData['product_filters'] = $this->filterConverter->convert(
            $request,
            json_encode($datagridViewFilters['f'])
        );

        $project = $this->projectBuilder->build($projectData);
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
     * Returns Projects in terms of search and options.
     * Options accept 'limit' => (int) and 'page' => (int) and 'user' => UserInterface.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function searchAction(Request $request)
    {
        $options = $request->query->get('options', ['limit' => 20, 'page' => 1]);
        $contributor = $this->tokenStorage->getToken()->getUser()->getUsername();

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
            $normalizedProject['completeness'] = $this->projectCompletenessRepository->getProjectCompleteness(
                $project,
                $contributor
            );

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
        $project = $this->projectRepository->findOneByIdentifier($identifier);

        $normalizedProject = $this->projectNormalizer->normalize($project, 'internal_api');

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
     *
     * @param string $identifier
     *
     * @Template("PimEnterpriseActivityManagerBundle:Project:filter-grid.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function showAction($identifier)
    {
        $project = $this->projectRepository->findOneByIdentifier($identifier);

        if (null === $project ||
            !$this->authorizationChecker->isGranted([ProjectVoter::OWN, ProjectVoter::CONTRIBUTE], $project)
        ) {
            return new RedirectResponse($this->router->generate('pim_enrich_product_index'));
        }

        return [
            'view' => $project->getDatagridView()
        ];
    }
}
