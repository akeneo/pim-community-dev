<?php

namespace PimEnterprise\Bundle\BaseConnectorBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Akeneo\Bundle\BatchBundle\Connector\Connector;

/**
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class PimEnterpriseBaseConnectorBundle extends Connector
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'PimBaseConnectorBundle';
    }
}
