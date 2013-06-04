<?php

namespace Oro\Bundle\UIBundle\Tests\Unit\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;

use Oro\Bundle\UIBundle\DependencyInjection\OroUIExtension;

class OroUIExtensionTest extends \PHPUnit_Framework_TestCase
{
    private $container;

    public function testLoad()
    {
        $this->container = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');

        $this->container->expects($this->once())
            ->method('getParameter')
            ->will($this->returnValue(array('Oro\Bundle\UIBundle\Tests\Unit\Fixture\UnitTestBundle')));

        $this->container->expects($this->any())
            ->method('setParameter');

        $extension = new OroUIExtension();

        $extensionConfig = array(
            array(
                'placeholders_blocks' => array(
                    array(
                        'name' => 'top_test_template',
                        'remove' => true
                    ),
                    array(
                        'name' => 'insert_template',
                        'placeholder' => 'new_position',
                        'action' => 'test_action'
                    ),
                )
            )
        );

        $extension->load($extensionConfig, $this->container);
    }
}
