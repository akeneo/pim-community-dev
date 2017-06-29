<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\Product;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\FilterBundle\Grid\Extension\Configuration as FilterConfiguration;
use Pim\Bundle\DataGridBundle\Datagrid\Configuration\Product\ConfigurationRegistry;
use Pim\Bundle\DataGridBundle\Datagrid\Configuration\Product\FiltersConfigurator as BaseFiltersConfigurator;
use PimEnterprise\Bundle\FilterBundle\Filter\Product\PermissionFilter;
use PimEnterprise\Bundle\FilterBundle\Filter\Product\ProjectCompletenessFilter;
use PimEnterprise\Component\TeamworkAssistant\Repository\ProjectRepositoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Override filters configurator to add is owner filter in product grid
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class FiltersConfigurator extends BaseFiltersConfigurator
{
    /** @var RequestStack */
    protected $stack;

    /** @var ProjectRepositoryInterface */
    protected $projectRepository;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var bool */
    protected $isProject = false;

    /** @var bool */
    protected $isProjectOwner = false;

    /**
     * @param ConfigurationRegistry      $registry
     * @param RequestStack               $stack
     * @param ProjectRepositoryInterface $projectRepository
     * @param TokenStorageInterface      $tokenStorage
     */
    public function __construct(
        ConfigurationRegistry $registry,
        RequestStack $stack,
        ProjectRepositoryInterface $projectRepository,
        TokenStorageInterface $tokenStorage
    ) {
        parent::__construct($registry);

        $this->stack = $stack;
        $this->projectRepository = $projectRepository;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(DatagridConfiguration $configuration)
    {
        parent::configure($configuration);

        $this->retrieveTeamworkAssistantInformations();

        $this->addIsOwnerFilter($configuration);
        $this->addProjectCompletenessFilter($configuration);
    }

    /**
     * Read the request stack to guess if the user is on a datagrid view, and especially on a
     * Teamwork Assistant project. If so, stores the information locally just for the time of the request.
     */
    protected function retrieveTeamworkAssistantInformations()
    {
        $currentRequest = $this->stack->getCurrentRequest();
        if (null === $currentRequest) {
            return;
        }

        $parameters = $currentRequest->get('product-grid');
        if (!isset($parameters['_parameters']['view']['id']) || '' === $parameters['_parameters']['view']['id']) {
            return;
        }

        $viewId = $parameters['_parameters']['view']['id'];
        $project = $this->projectRepository->findOneBy(['datagridView' => $viewId]);
        if (null === $project) {
            return;
        }

        $this->isProject = true;

        if ($this->tokenStorage->getToken()->getUsername() === $project->getOwner()->getUsername()) {
            $this->isProjectOwner = true;
        }
    }

    /**
     * Add the is owner filter in the datagrid configuration
     *
     * @param DatagridConfiguration $configuration
     */
    protected function addIsOwnerFilter(DatagridConfiguration $configuration)
    {
        $filter = [
            'type'      => 'product_permission',
            'ftype'     => 'choice',
            'data_name' => 'permissions',
            'label'     => 'pimee_workflow.product.permission.label',
            'options'   => [
                'field_options' => [
                    'multiple' => false,
                    'choices'  => [
                        PermissionFilter::OWN  => 'pimee_workflow.product.permission.own',
                        PermissionFilter::EDIT => 'pimee_workflow.product.permission.edit',
                        PermissionFilter::VIEW => 'pimee_workflow.product.permission.view',
                    ]
                ]
            ]
        ];

        $configuration->offsetSetByPath(
            sprintf('%s[%s]', FilterConfiguration::COLUMNS_PATH, 'permissions'),
            $filter
        );
    }

    /**
     * Add the Teamwork Assistant project completeness filter in the datagrid configuration.
     * The filter is only added if the user is currently on a Teamwork Assistant project.
     *
     * @param DatagridConfiguration $configuration
     */
    protected function addProjectCompletenessFilter(DatagridConfiguration $configuration)
    {
        if (!$this->isProject) {
            return;
        }

        $choices = [
            ProjectCompletenessFilter::CONTRIBUTOR_TODO        => 'teamwork_assistant.datagrid.contributor_todo',
            ProjectCompletenessFilter::CONTRIBUTOR_IN_PROGRESS => 'teamwork_assistant.datagrid.contributor_in_progress',
            ProjectCompletenessFilter::CONTRIBUTOR_DONE        => 'teamwork_assistant.datagrid.contributor_done',
        ];

        if ($this->isProjectOwner) {
            $choices[ProjectCompletenessFilter::OWNER_TODO] = 'teamwork_assistant.datagrid.owner_todo';
            $choices[ProjectCompletenessFilter::OWNER_IN_PROGRESS] = 'teamwork_assistant.datagrid.owner_in_progress';
            $choices[ProjectCompletenessFilter::OWNER_DONE] = 'teamwork_assistant.datagrid.owner_done';
        }

        $filter = [
            'type'      => 'project_completeness',
            'ftype'     => 'choice',
            'data_name' => 'project_completeness',
            'label'     => 'teamwork_assistant.datagrid.project_completeness',
            'options'   => [
                'field_options' => [
                    'multiple' => false,
                    'choices'  => $choices
                ]
            ]
        ];

        $configuration->offsetSetByPath(
            sprintf('%s[%s]', FilterConfiguration::COLUMNS_PATH, 'project_completeness'),
            $filter
        );
    }
}
