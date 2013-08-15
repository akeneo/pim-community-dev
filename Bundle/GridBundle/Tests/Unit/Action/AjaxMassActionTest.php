<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Action;

use Oro\Bundle\GridBundle\Action\MassAction\AjaxMassAction;

class AjaxMassActionTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $massAction = new AjaxMassAction(array());

        $this->assertEquals($massAction->getOption('route'), 'oro_grid_mass_action');
        $this->assertEquals($massAction->getOption('route_parameters'), array());

        foreach (array('name', 'aclResource', 'label') as $opt) {
            $this->assertNull($massAction->{'get'.ucfirst($opt)}());
        }
    }
}
