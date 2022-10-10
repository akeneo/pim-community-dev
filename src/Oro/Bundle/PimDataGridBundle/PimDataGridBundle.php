<?php

namespace Oro\Bundle\PimDataGridBundle;

use Oro\Bundle\PimDataGridBundle\DependencyInjection\Compiler;
use Oro\Bundle\PimDataGridBundle\DependencyInjection\Compiler\AddAttributeTypesPass;
use Oro\Bundle\PimDataGridBundle\DependencyInjection\Compiler\AddFilterTypesPass;
use Oro\Bundle\PimDataGridBundle\DependencyInjection\Compiler\AddMassActionHandlersPass;
use Oro\Bundle\PimDataGridBundle\DependencyInjection\Compiler\AddSelectorsPass;
use Oro\Bundle\PimDataGridBundle\DependencyInjection\Compiler\AddSortersPass;
use Oro\Bundle\PimDataGridBundle\DependencyInjection\Compiler\ConfigurationPass;
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
            ->addCompilerPass(new AddFilterTypesPass())
            ->addCompilerPass(new AddAttributeTypesPass())
            ->addCompilerPass(new AddSelectorsPass())
            ->addCompilerPass(new AddSortersPass())
            ->addCompilerPass(new AddMassActionHandlersPass())
            ->addCompilerPass(new ConfigurationPass());
    }
}
