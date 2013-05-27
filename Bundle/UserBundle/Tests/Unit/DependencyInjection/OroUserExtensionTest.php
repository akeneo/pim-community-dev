<?php

namespace Oro\Bundle\UserBundle\Tests\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Parser;

use Oro\Bundle\UserBundle\DependencyInjection\OroUserExtension;

class OroUserExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerBuilder
     */
    protected $configuration;

    public function testLoadWithDefaults()
    {
        $this->createEmptyConfiguration();

        $this->assertParameter(array('no-reply@example.com' => 'Oro Admin'), 'oro_user.email');
        $this->assertParameter(86400, 'oro_user.reset.ttl');
    }

    public function testLoad()
    {
        $this->createFullConfiguration();

        $this->assertParameter(array('admin@acme.org' => 'Acme Corp'), 'oro_user.email');
        $this->assertParameter(1800, 'oro_user.reset.ttl');
    }

    protected function createEmptyConfiguration()
    {
        $this->configuration = new ContainerBuilder();

        $loader = new OroUserExtension();
        $config = $this->getEmptyConfig();

        $loader->load(array($config), $this->configuration);

        $this->assertTrue($this->configuration instanceof ContainerBuilder);
    }

    protected function createFullConfiguration()
    {
        $this->configuration = new ContainerBuilder();

        $loader = new OroUserExtension();
        $config = $this->getFullConfig();

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

    protected function getFullConfig()
    {
        $yaml = <<<EOF
email:
    address: admin@acme.org
    name: Acme Corp
reset:
    ttl: 1800
EOF;
        $parser = new Parser();

        return  $parser->parse($yaml);
    }

    /**
     * @param mixed  $value
     * @param string $key
     */
    protected function assertParameter($value, $key)
    {
        $this->assertEquals($value, $this->configuration->getParameter($key), sprintf('%s parameter is correct', $key));
    }

    protected function tearDown()
    {
        unset($this->configuration);
    }
}
