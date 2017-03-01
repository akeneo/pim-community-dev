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
    private $stack;

    /** @var ProjectRepositoryInterface */
    private $projectRepository;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    private $isProject = null;
    private $isProjectOwner = null;

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

    protected function retrieveTeamworkAssistantInformations()
    {
        $parameters = $request = $this->stack->getCurrentRequest()->get('product-grid');

        if (!isset($parameters['_parameters']['view']['id']) || empty($parameters['_parameters']['view']['id'])) {
            return;
        }

        $viewId = $parameters['_parameters']['view']['id'];
        $project = $this->projectRepository->findOneBy(['datagridView' => $viewId]);

        if (null === $project) {
            return;
        }

        $this->isProject = (null === $this->isProject) ? true : $this->isProject;

        if ($this->tokenStorage->getToken()->getUsername() === $project->getOwner()->getUsername()) {
            $this->isProjectOwner = (null === $this->isProjectOwner) ? true : $this->isProjectOwner;
        }
    }

    /**
     * Add the is owner filter in the datagrid configuration
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
            sprintf('%s[%s]', FilterConfiguration::COLUMNS_PATH, 'permission'),
            $filter
        );
    }

    /**
     * @param $configuration
     */
    protected function addProjectCompletenessFilter($configuration)
    {
        if (!$this->isProject) {
            return;
        }

        $choices = [
            ProjectCompletenessFilter::CONTRIBUTOR_TODO        => 'TODO (Contributor)',
            ProjectCompletenessFilter::CONTRIBUTOR_IN_PROGRESS => 'IN PROGRESS (Contributor)',
            ProjectCompletenessFilter::CONTRIBUTOR_DONE        => 'DONE (Contributor)',
        ];

        if ($this->isProjectOwner) {
            $choices[ProjectCompletenessFilter::OWNER_TODO] = 'TODO (Owner)';
            $choices[ProjectCompletenessFilter::OWNER_IN_PROGRESS] = 'IN PROGRESS (Owner)';
            $choices[ProjectCompletenessFilter::OWNER_DONE] = 'DONE (Owner)';
        }

        $filter = [
            'type'      => 'project_completeness',
            'ftype'     => 'choice',
            'data_name' => 'project_completeness',
            'label'     => 'PROJECT COMPLETENESS',
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
