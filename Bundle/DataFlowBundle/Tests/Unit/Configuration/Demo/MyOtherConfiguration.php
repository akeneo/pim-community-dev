<?php
namespace Oro\Bundle\DataFlowBundle\Tests\Unit\Configuration\Demo;

use Oro\Bundle\DataFlowBundle\Configuration\AbstractConfiguration;
use JMS\Serializer\Annotation\Type;

/**
 * Demo configuration
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
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
