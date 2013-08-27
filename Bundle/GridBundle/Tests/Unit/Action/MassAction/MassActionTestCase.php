<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Action\MassAction;

use Oro\Bundle\GridBundle\Action\MassAction\MassActionInterface;

abstract class MassActionTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array $options
     * @return MassActionInterface
     */
    abstract protected function createMassAction(array $options);

    /**
     * @return array
     */
    abstract public function constructDataProvider();

    /**
     * @param array $expectedOptions
     * @param array $inputOptions
     * @dataProvider constructDataProvider
     */
    public function testConstruct(array $expectedOptions, array $inputOptions)
    {
        $massAction = $this->createMassAction($inputOptions);
        $this->assertEquals($expectedOptions, $massAction->getOptions());
    }
}
