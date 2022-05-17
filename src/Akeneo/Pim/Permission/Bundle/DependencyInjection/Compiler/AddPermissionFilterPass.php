<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Bundle\DependencyInjection\Compiler;

use Akeneo\Pim\Permission\Bundle\Datagrid\Filter\PermissionFilter;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class AddPermissionFilterPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $configProdiverDefinition = $container->getDefinition('oro_datagrid.configuration.provider');
        $config = $configProdiverDefinition->getArgument(0);
        $config = array_merge_recursive(
            $config,
            [
                'product-grid' => [
                    'filters' => [
                        'columns' => [
                            'permissions' => [
                                'type' => 'product_permission',
                                'ftype' => 'choice',
                                'data_name' => 'permissions',
                                'label' => 'Permissions',
                                'options' => [
                                    'field_options' => [
                                        'multiple' => false,
                                        'choices' => [
                                            'pimee_workflow.product.permission.own' => PermissionFilter::OWN,
                                            'pimee_workflow.product.permission.edit' => PermissionFilter::EDIT,
                                            'pimee_workflow.product.permission.view' => PermissionFilter::VIEW,
                                        ],
                                    ],
                                ],
                                'feature_flag' => 'permission'
                            ],
                        ],
                    ],
                ],
            ]
        );

        $configProdiverDefinition->replaceArgument(0, $config);
    }
}
