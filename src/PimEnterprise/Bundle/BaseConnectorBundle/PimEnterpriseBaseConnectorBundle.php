<?php

namespace PimEnterprise\Bundle\BaseConnectorBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Akeneo\Bundle\BatchBundle\Connector\Connector;

/**
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimEnterpriseBaseConnectorBundle extends Connector
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'PimBaseConnectorBundle';
    }
}
