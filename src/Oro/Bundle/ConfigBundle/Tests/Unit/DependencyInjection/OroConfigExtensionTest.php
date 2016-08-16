<?php

namespace Oro\Bundle\ConfigBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Parser;

class OroConfigExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerBuilder
     */
    protected $configuration;

    public function testLoadWithDefaults()
    {
        $this->createEmptyConfiguration();
    }

    public function testLoad()
    {
        $extension = new OroConfigExtension();
        $configs = [];

        $container = $this->createMock('Symfony\Component\DependencyInjection\ContainerBuilder');
        $container->expects($this->any())
            ->method('setParameter')
            ->will(
                $this->returnCallback(
                    function ($name, $value) use (&$isCalled) {
                        if ($name == 'oro_config' && is_array($value)) {
                            $isCalled = true;
                        }
                    }
                )
            );

        $container->expects($this->any())
            ->method('getParameter')
            ->with('kernel.bundles')
            ->will($this->returnValue([]));

        $extension->load($configs, $container);
    }

    protected function createEmptyConfiguration()
    {
        $this->configuration = new ContainerBuilder();

        $this->configuration->setParameter('kernel.bundles', []);

        $loader = new OroConfigExtension();
        $config = $this->getEmptyConfig();

        $loader->load([$config], $this->configuration);

        $this->assertTrue($this->configuration instanceof ContainerBuilder);
    }

    /**
     * @return array
     */
    protected function getEmptyConfig()
    {
        $yaml = '';
        $parser = new Parser();

        return $parser->parse($yaml);
    }

    /**
     * @param mixed  $value
     * @param string $key
     */
    protected function assertParameter($value, $key)
    {
        $this->assertEquals($value, $this->configuration->getParameter($key), sprintf('%s parameter is correct', $key));
    }

    protected function getContainer(array $config = [])
    {
        $container = new ContainerBuilder();
        $loader = new OroConfigExtension();

        $container->addCompilerPass(new Compiler\ConfigPass());
        $container->setParameter('kernel.bundles', []);
        $loader->load($config, $container);

        $container->register(
            'doctrine.orm.entity_manager',
            $this->getMockClass('Doctrine\Common\Persistence\ObjectManager')
        );
        $container->register(
            'security.token_storage',
            $this->getMockClass('Symfony\Component\Security\Core\TokenStorageInterface')
        );
        $container->compile();

        return $container;
    }

    protected function tearDown()
    {
        unset($this->configuration);
    }
}
