<?php

namespace Oro\Bundle\UIBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\UIBundle\DependencyInjection\OroUIExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OroUIExtensionTest extends \PHPUnit_Framework_TestCase
{
    private $container;

    public function testLoad()
    {
        $this->container = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');

        $this->container->expects($this->once())
            ->method('getParameter')
            ->will($this->returnValue(['Oro\Bundle\UIBundle\Tests\Unit\Fixture\UnitTestBundle']));

        $this->container->expects($this->any())
            ->method('setParameter');

        $extension = new OroUIExtension();

        $extensionConfig = [
            [
                'placeholders_items' => [
                    'top_block' => [
                        'items' => [
                            'top_test_template' => [
                                'remove' => true
                            ],
                            'insert_template' => [
                                'order' => 100
                            ],
                        ]
                    ]

                ]
            ]
        ];

        $extension->load($extensionConfig, $this->container);
    }
}
