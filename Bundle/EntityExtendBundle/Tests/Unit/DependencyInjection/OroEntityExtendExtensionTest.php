<?php

namespace Oro\Bundle\EntityExtendBundle\Tests\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Parser;

use Oro\Bundle\EntityExtendBundle\DependencyInjection\OroEntityExtendExtension;

class OroEntityExtendExtensionTest extends \PHPUnit_Framework_TestCase
{
    protected $configuration;

    public function testLoadWithDefaults()
    {
        $this->configuration = new ContainerBuilder();

        $parser = new Parser();
        $yml    = <<<EOF
cache_dir: 'app/entity'
EOF;
        $config = $parser->parse($yml);

        $loader = new OroEntityExtendExtension();
        $loader->load(array($config), $this->configuration);

        $this->assertTrue($this->configuration instanceof ContainerBuilder);
        $this->assertParameter('app/entity/annotation', 'oro_entity_extend.cache_dir.annotation');

    }

    protected function assertParameter($value, $key)
    {
        $this->assertEquals($value, $this->configuration->getParameter($key), sprintf('%s parameter is correct', $key));
    }
}
