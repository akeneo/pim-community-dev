<?php
namespace Oro\Bundle\DataFlowBundle\Tests\Unit\Configuration\Demo;

use Oro\Bundle\DataFlowBundle\Configuration\AbstractConfiguration;
use JMS\Serializer\Annotation\Type;

/**
 * Demo configuration
 *
 *
 */
class MyOtherConfiguration extends AbstractConfiguration
{
    /**
     * {@inheritDoc}
     */
    public function getFormTypeServiceId()
    {
        return "my_other_configuration";
    }
}
