<?php

namespace Pim\Bundle\BatchBundle\Tests\Unit\DependencyInjection\Compiler;

use Pim\Bundle\BatchBundle\DependencyInjection\Compiler\PushBatchLogHandlerPass;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PushBatchLogHandlerPassTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->pass = new PushBatchLogHandlerPass;
    }

    public function testProcessWithBatchChannel()
    {
        $logger    = new Definition;
        $container = $this->getContainerBuilderMock($logger);

        $container->expects($this->any())
            ->method('getDefinition')
            ->will($this->returnValueMap(array(array('monolog.logger.batch', $logger))));

        $this->pass->process($container);

        $calls = $logger->getMethodCalls();
        $this->assertEquals('pushHandler', $calls[0][0]);
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $calls[0][1][0]);
        $this->assertAttributeEquals('pim_batch.logger.batch_log_handler', 'id', $calls[0][1][0]);
    }

    public function testProcessWithoutBatchChannel()
    {
        $container = $this->getContainerBuilderMock();

        $container->expects($this->never())
            ->method('getDefinition');

        $this->pass->process($container);
    }

    private function getContainerBuilderMock(Definition $logger = null)
    {
        $container = $this
            ->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $container->expects($this->any())
            ->method('has')
            ->will($this->returnValueMap(array(array('monolog.logger.batch', null !== $logger))));

        return $container;
    }
}
