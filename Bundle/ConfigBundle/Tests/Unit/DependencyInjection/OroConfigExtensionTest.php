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

    public function testCompilerPass()
    {
        /**
         * @TODO FIX
         */
        $this->markTestSkipped('FIX ERRORS');
        $container = $this->getContainer();

        $this->assertTrue($container->hasDefinition('oro_config.user'));
    }

    protected function createEmptyConfiguration()
    {
        $this->configuration = new ContainerBuilder();

        $this->configuration->setParameter('kernel.bundles', array());

        $loader = new OroConfigExtension();
        $config = $this->getEmptyConfig();

        $loader->load(array($config), $this->configuration);

        $this->assertTrue($this->configuration instanceof ContainerBuilder);
    }

    /**
     * @return array
     */
    protected function getEmptyConfig()
    {
        $yaml   = '';
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

    protected function getContainer(array $config = array())
    {
        $container = new ContainerBuilder();
        $loader    = new OroConfigExtension();

        $container->addCompilerPass(new Compiler\ConfigPass());
        $container->setParameter('kernel.bundles', array());
        $loader->load($config, $container);

        $container->register(
            'doctrine.orm.entity_manager',
            $this->getMockClass('Doctrine\Common\Persistence\ObjectManager')
        );
        $container->register(
            'security.context',
            $this->getMockClass('Symfony\Component\Security\Core\SecurityContextInterface')
        );
        $container->compile();

        return $container;
    }

    protected function tearDown()
    {
        unset($this->configuration);
    }
}
