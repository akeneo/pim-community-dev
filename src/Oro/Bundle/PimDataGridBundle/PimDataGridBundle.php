<?php

namespace Oro\Bundle\PimDataGridBundle;

use Oro\Bundle\PimDataGridBundle\DependencyInjection\Compiler;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

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
        $container
            ->addCompilerPass(new Compiler\AddFilterTypesPass())
            ->addCompilerPass(new Compiler\AddAttributeTypesPass())
            ->addCompilerPass(new Compiler\AddSelectorsPass())
            ->addCompilerPass(new Compiler\AddSortersPass())
            ->addCompilerPass(new Compiler\AddMassActionHandlersPass())
            ->addCompilerPass(new Compiler\ConfigurationPass());
    }
}
