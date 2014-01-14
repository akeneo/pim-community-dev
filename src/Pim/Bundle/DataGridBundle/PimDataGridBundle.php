<?php

namespace Pim\Bundle\DataGridBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Pim\Bundle\DataGridBundle\DependencyInjection\Compiler;

/**
 * Pim DataGrid Bundle
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimDataGridBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container
            ->addCompilerPass(new Compiler\AddFilterTypesPass())
            ->addCompilerPass(new Compiler\AddAttributeTypesPass())
            ->addCompilerPass(new Compiler\AddExportActionsPass());
    }
}
