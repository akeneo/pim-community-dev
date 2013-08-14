<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Action;

use Oro\Bundle\GridBundle\Action\MassAction\DeleteMassAction;

class DeleteMassActionTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $mAction = new DeleteMassAction(array());

        $this->assertEquals('delete', $mAction->getName());
        $this->assertEquals('oro_grid.mass_action.handler.delete', $mAction->getOption('handler'));
    }
}
