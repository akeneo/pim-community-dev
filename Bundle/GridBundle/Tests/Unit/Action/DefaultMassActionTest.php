<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Action;

use Oro\Bundle\GridBundle\Action\MassAction\DefaultMassAction;

class DefaultMassActionTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $mAction = new DefaultMassAction(array());

        $this->assertEquals($mAction->getRoute(), 'oro_grid_mass_action');

        foreach (array('name', 'aclResource', 'label') as $opt) {
            $this->assertNull($mAction->{'get'.ucfirst($opt)}());
        }

        $this->assertCount(1, $mAction->getOptions());
    }
}
