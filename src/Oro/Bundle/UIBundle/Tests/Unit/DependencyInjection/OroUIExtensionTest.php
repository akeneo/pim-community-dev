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
                'placeholders_items' => array(
                    'top_block' => array(
                        'items' => array(
                            'top_test_template' => array(
                                'remove' => true
                            ),
                            'insert_template' => array(
                                'order' => 100
                            ),
                        )
                    )

                )
            )
        );

        $extension->load($extensionConfig, $this->container);
    }
}
