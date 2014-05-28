<?php

namespace PimEnterprise\Bundle\DataGridBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use PimEnterprise\Bundle\DataGridBundle\DependencyInjection\Compiler;

/**
 * PIM Enterprise Datagrid Bundle
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class PimEnterpriseDataGridBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container
            ->addCompilerPass(new Compiler\AddFilterTypesPass())
            ->addCompilerPass(new Compiler\AddSortersPass());
    }
}
