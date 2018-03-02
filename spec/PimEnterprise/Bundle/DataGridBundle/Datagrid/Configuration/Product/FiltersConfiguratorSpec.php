<?php

namespace spec\PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\Product;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\FilterBundle\Grid\Extension\Configuration as FilterConfiguration;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGridBundle\Datagrid\Configuration\ConfiguratorInterface;
use PimEnterprise\Component\TeamworkAssistant\Repository\ProjectRepositoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class FiltersConfiguratorSpec extends ObjectBehavior
{
    function let(
        ConfiguratorInterface $filtersConfigurator,
        RequestStack $stack,
        ProjectRepositoryInterface $projectRepository,
        TokenStorageInterface $tokenStorage
    ) {
        $this->beConstructedWith($filtersConfigurator, $stack, $projectRepository, $tokenStorage);
    }

    function it_is_a_configurator()
    {
        $this->shouldImplement('Pim\Bundle\DataGridBundle\Datagrid\Configuration\ConfiguratorInterface');
    }

    function it_adds_is_owner_filter(
        $filtersConfigurator,
        DatagridConfiguration $configuration
    ) {
        $filtersConfigurator->configure($configuration)->shouldBeCalled();

        $columnConfPath = sprintf('%s[%s]', FilterConfiguration::COLUMNS_PATH, 'permissions');
        $expectedConf = [
            'type'      => 'product_permission',
            'ftype'     => 'choice',
            'data_name' => 'permissions',
            'label'     => 'pimee_workflow.product.permission.label',
            'options'   => [
                'field_options' => [
                    'multiple' => false,
                    'choices'  => [
                        'pimee_workflow.product.permission.own' => 3,
                        'pimee_workflow.product.permission.edit' => 2,
                        'pimee_workflow.product.permission.view' => 1,
                    ]
                ]
            ]
        ];
        $configuration->offsetSetByPath($columnConfPath, $expectedConf)->shouldBeCalled();

        $this->configure($configuration);
    }
}
