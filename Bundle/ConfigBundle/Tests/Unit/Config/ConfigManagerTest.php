<?php

namespace Oro\Bundle\ConfigBundle\Config;

use Doctrine\Common\Persistence\ObjectManager;

class ConfigManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigManager
     */
    protected $object;

    /**
     * @var ObjectManager
     */
    protected $om;

    /**
     * @var array
     */
    protected $settings = array(
        'oro_user' => array(
            'greeting' => array(
                'value' => true,
                'type'  => 'boolean',
            ),
            'level'    => array(
                'value' => 20,
                'type'  => 'scalar',
            )
        ),
        'oro_test' => array(
            'anysetting' => array(
                'value' => 'anyvalue',
                'type'  => 'scalar',
            ),
        ),
    );

    protected function setUp()
    {
        $this->markTestSkipped('');
        if (!interface_exists('Doctrine\Common\Persistence\ObjectManager')) {
            $this->markTestSkipped('Doctrine Common has to be installed for this test to run.');
        }

        $this->om = $this->getMock('Doctrine\Common\Persistence\ObjectManager');

        $this->object = $this->getMock('Oro\Bundle\ConfigBundle\Config\ConfigManager');
            new ConfigManager($this->om, $this->settings);
    }

    public function testGet()
    {
        $object = $this->object;

        $this->assertEquals($this->settings['oro_user']['greeting']['value'], $object->get('oro_user.greeting'));
        $this->assertEquals($this->settings['oro_user']['level']['value'], $object->get('oro_user.level'));
        $this->assertEquals($this->settings['oro_test']['anysetting']['value'], $object->get('oro_test.anysetting'));
        $this->assertNull($object->get('oro_test.nosetting'));
        $this->assertNull($object->get('noservice.nosetting'));
    }
}
