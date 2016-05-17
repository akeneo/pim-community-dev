<?php

namespace Pim\Bundle\ConnectorBundle;

use Pim\Bundle\ConnectorBundle\DependencyInjection\Compiler\RegisterConverterPass;
use Pim\Bundle\ConnectorBundle\DependencyInjection\Compiler\RegisterFilterConfiguratorPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Connector bundle
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimConnectorBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container
            ->addCompilerPass(new RegisterConverterPass())
            ->addCompilerPass(new RegisterFilterConfiguratorPass())
        ;
    }
}
