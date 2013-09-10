<?php
namespace Oro\Bundle\SearchBundle\Tests\Unit\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;

use Oro\Bundle\SearchBundle\DependencyInjection\OroSearchExtension;

class OroSearchExtensionTest extends \PHPUnit_Framework_TestCase
{
    private $container;

    public function setUp()
    {
        $this->container = new ContainerBuilder();
        $this->container = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');

        $params = array(
            'kernel.bundles'     => array('Oro\Bundle\SearchBundle\Tests\Unit\Fixture\TestBundle'),
            'kernel.environment' => 'test',
        );

        $this->container->expects($this->any())
            ->method('getParameter')
            ->with(
                $this->logicalOr(
                    $this->equalTo('kernel.bundles'),
                    $this->equalTo('kernel.environment')
                )
            )
            ->will(
                $this->returnCallback(
                    function ($param) use (&$params) {
                        return $params[$param];
                    }
                )
            );

        $this->container->expects($this->any())
            ->method('setParameter');
    }

    public function testGetAlias()
    {
        $searchExtension = new OroSearchExtension(array(), $this->container);
        $this->assertEquals('oro_search', $searchExtension->getAlias());
    }

    public function testLoadWithConfigInFiles()
    {
        $searchExtension = new OroSearchExtension();
        $config = array(
            'oro_search' => array(
                'engine'          => 'orm',
                'realtime_update' => true
            )
        );
        $searchExtension->load($config, $this->container);
    }

    public function testLoadWithConfigPaths()
    {
        $searchExtension = new OroSearchExtension();
        $config = array(
            'oro_search' => array(
                'engine'          => 'orm',
                'realtime_update' => true,
                'entities_config' => array(
                    'Oro\Bundle\DataBundle\Entity\Product' => array(
                        'alias'             => 'test_alias',
                        'search_template'   => 'test_template',
                        'fields'            => array(
                            array(
                                'name'          => 'name',
                                'target_type'   => 'string',
                                'target_fields' => array('name', 'all_data')
                            )
                        )
                    )
                )
            )
        );
        $searchExtension->load($config, $this->container);
    }

    public function testLoadWithEngineOrm()
    {
        $searchExtension = new OroSearchExtension();
        $config = array(
            'oro_search' => array(
                'engine'          => 'orm',
                'realtime_update' => true,
                'engine_orm'      => array('pro_pgSql')
            )
        );
        $searchExtension->load($config, $this->container);
    }
}
