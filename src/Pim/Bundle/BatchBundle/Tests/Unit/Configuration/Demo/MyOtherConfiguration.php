<?php
namespace Pim\Bundle\BatchBundle\Tests\Unit\Configuration\Demo;

use Pim\Bundle\BatchBundle\Configuration\AbstractConfiguration;

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
