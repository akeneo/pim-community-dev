<?php
namespace Oro\Bundle\UserBundle\Tests\Unit\Acl\ResourceReader;

use Oro\Bundle\UserBundle\Acl\ResourceReader\ConfigReader;

class ConfigReaderTest extends \PHPUnit_Framework_TestCase
{
    private $reader;

    public function setUp()
    {
        $this->reader = new ConfigReader(
            array(
                 'Oro\Bundle\UserBundle\Tests\Unit\Fixture\FixtureBundle'
            )
        );
    }

    public function testGetConfigResources()
    {
        $output = $this->reader->getConfigResources();
        $acl = $output['test_controller'];
        $this->assertEquals('test_controller', $acl->getId());
        $this->assertEquals('Test controller', $acl->getName());
    }

    public function testGetMethodAclId()
    {
        $output = $this->reader->getMethodAclId(
            'Oro\Bundle\UserBundle\Tests\Unit\Fixture\ConfigController\ConfigController',
            'testAction'
        );
        $this->assertEquals('test_controller', $output);
    }

    public function testGetConfigResourcesForDirectory()
    {
        $reflection = new \ReflectionClass('\Oro\Bundle\UserBundle\Tests\Unit\Fixture\FixtureBundle');
        $output = $this->reader->getConfigResources($reflection->getFilename());
        $acl = $output['test_controller'];
        $this->assertEquals('testAction', $acl->getMethod());
    }
}
