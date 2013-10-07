<?php

namespace Oro\Bundle\CronBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Parser;

class OroCronExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerBuilder
     */
    protected $configuration;

    public function testLoadWithDefaults()
    {
        $this->createEmptyConfiguration();

        $this->assertParameter(5, 'oro_cron.max_jobs');
        $this->assertParameter(true, 'oro_cron.jms_statistics');
    }

    public function testLoad()
    {
        $this->createFullConfiguration();

        $this->assertParameter(10, 'oro_cron.max_jobs');
        $this->assertParameter(false, 'oro_cron.jms_statistics');
    }

    protected function createEmptyConfiguration()
    {
        $this->configuration = new ContainerBuilder();

        $loader = new OroCronExtension();
        $config = $this->getEmptyConfig();

        $loader->load(array($config), $this->configuration);

        $this->assertTrue($this->configuration instanceof ContainerBuilder);
    }

    protected function createFullConfiguration()
    {
        $this->configuration = new ContainerBuilder();

        $loader = new OroCronExtension();
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
max_concurrent_jobs: 10
jms_statistics: false
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
