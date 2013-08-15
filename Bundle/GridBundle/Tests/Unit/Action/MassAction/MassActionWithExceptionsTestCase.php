<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Action\MassAction;

use Oro\Bundle\GridBundle\Action\MassAction\MassActionInterface;

abstract class MassActionWithExceptionsTestCase extends MassActionTestCase
{
    /**
     * @return array
     */
    abstract public function constructExceptionDataProvider();

    /**
     * @param string $exceptionName
     * @param string $exceptionMessage
     * @param array $inputOptions
     * @dataProvider constructExceptionDataProvider
     */
    public function testConstructException($exceptionName, $exceptionMessage, array $inputOptions)
    {
        $this->setExpectedException($exceptionName, $exceptionMessage);
        $this->createMassAction($inputOptions);
    }
}
