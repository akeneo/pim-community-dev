<?php

namespace Oro\Bundle\NavigationBundle\Tests\Unit\Title\TitleReader;

use Oro\Bundle\NavigationBundle\Title\TitleReader\ConfigReader;

class ConfigReaderTest extends \PHPUnit_Framework_TestCase
{
    const TEST_ROUTE = 'test_route';

    /**
     * @var ConfigReader
     */
    private $reader;

    public function setUp()
    {
        $this->reader = new ConfigReader(array(self::TEST_ROUTE => 'Test title template'));
    }

    public function testGetDataSuccess()
    {
        try {
            $data = $this->reader->getData(array(self::TEST_ROUTE => 'Test route data'));

            $this->assertInternalType('array', $data);
            $this->assertCount(1, $data);
        } catch (\Exception $e) {
            $this->assertInstanceOf('Symfony\Component\Config\Definition\Exception\InvalidConfigurationException', $e);
        }
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testGetDataFailed()
    {
        $this->reader->getData(array());
    }
}
