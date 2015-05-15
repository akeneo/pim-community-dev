<?php

namespace Pim\Bundle\BaseConnectorBundle;

use Akeneo\Bundle\BatchBundle\Connector\Connector;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Base connector bundle
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimBaseConnectorBundle extends Connector
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container
            ->addCompilerPass(new DependencyInjection\Compiler\RegisterArchiversPass());
    }
}
