<?php

namespace Oro\Bundle\ConfigBundle\Tests\Unit\Config;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\PreloadedExtension;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\ConfigBundle\DependencyInjection\SystemConfiguration\ProcessorDecorator;
use Oro\Bundle\ConfigBundle\Entity\ConfigValue;
use Oro\Bundle\ConfigBundle\Form\Type\FormFieldType;
use Oro\Bundle\ConfigBundle\Form\Type\FormType;
use Oro\Bundle\ConfigBundle\Provider\SystemConfigurationFormProvider;
use Oro\Bundle\FormBundle\Form\Extension\DataBlockExtension;

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

    /**
     * @var array
     */
    protected $loadedSettings = array(
        'oro_user' => array(
            'level'    => array(
                'value' => 2000,
                'type'  => 'scalar',
            )
        ),
    );

    protected function setUp()
    {
        if (!interface_exists('Doctrine\Common\Persistence\ObjectManager')) {
            $this->markTestSkipped('Doctrine Common has to be installed for this test to run.');
        }

        $this->om = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $this->object = new ConfigManager($this->om, $this->settings);
    }

    /**
     * Test get loaded settings
     */
    public function testGetLoaded()
    {
        $loadedSettings = $this->loadedSettings;

        $repository = $this->getMockBuilder('Oro\Bundle\ConfigBundle\Entity\Repository\ConfigRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repository->expects($this->once())
            ->method('loadSettings')
            ->with('app', 0, null)
            ->will($this->returnValue($loadedSettings));

        $this->om
            ->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($repository));

        $object = $this->object;

        $this->assertEquals($this->loadedSettings['oro_user']['level']['value'], $object->get('oro_user.level'));
        $this->assertEquals($this->settings['oro_user']['greeting']['value'], $object->get('oro_user.greeting'));

        $this->assertNull($object->get('oro_test.nosetting'));
        $this->assertNull($object->get('noservice.nosetting'));
    }

    /**
     * Test default settings condition
     */
    public function testGetDefaultSettings()
    {
        $object = $this->getMock(
            'Oro\Bundle\ConfigBundle\Config\ConfigManager',
            array('loadStoredSettings'),
            array($this->om, $this->settings)
        );

        $this->assertEquals($this->settings['oro_user']['greeting']['value'], $object->get('oro_user.greeting', true));
        $this->assertEquals($this->settings['oro_user']['level']['value'], $object->get('oro_user.level', true));
        $this->assertEquals(
            $this->settings['oro_test']['anysetting']['value'],
            $object->get('oro_test.anysetting', true)
        );
    }

    /**
     * Test form pre-fill
     */
    public function testGetSettingsByForm()
    {
        $object = $this->getMock(
            'Oro\Bundle\ConfigBundle\Config\ConfigManager',
            array('get'),
            array($this->om, $this->settings)
        );

        $testSetting = array(
            'value' => 'test',
        );

        $object->expects($this->at(0))
            ->method('get')
            ->with('some_field')
            ->will($this->returnValue($testSetting));

        $provider = $this->getProviderWithConfigLoaded(__DIR__ . '/../Fixtures/Provider/good_definition.yml');
        $form = $provider->getForm('third_group');

        $settings = $object->getSettingsByForm($form);
        $this->assertArrayHasKey('some_field', $settings);
        $this->assertCount(2, $settings);
        $this->assertEquals($testSetting['value'], $settings['some_field']['value']);
    }

    /**
     * Test saving settings
     */
    public function testSave()
    {
        $settings = array(
            'oro_user___level' => array(
                'value' => 50,
            ),
        );

        $removed = array(
            'oro_user___greeting' => array(
                'value' => 'new value',
            ),
        );

        $object = $this->getMock(
            'Oro\Bundle\ConfigBundle\Config\ConfigManager',
            array('getChanged'),
            array($this->om, $this->settings)
        );

        $changes = array(
            $settings, $removed
        );

        $object->expects($this->once())
            ->method('getChanged')
            ->with($this->equalTo($settings))
            ->will($this->returnValue($changes));

        $configMock = $this->getMock('Oro\Bundle\ConfigBundle\Entity\Config');
        $configMock->expects($this->once())
            ->method('getOrCreateValue')
            ->will($this->returnValue(new ConfigValue()));
        $configMock->expects($this->once())
            ->method('getValues')
            ->will($this->returnValue(new ArrayCollection()));

        $valueRepository = $this->getMockBuilder('Oro\Bundle\ConfigBundle\Entity\Repository\ConfigValueRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $repository = $this->getMockBuilder('Oro\Bundle\ConfigBundle\Entity\Repository\ConfigRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $repository->expects($this->once())
            ->method('getByEntity')
            ->with('app', 0)
            ->will($this->returnValue($configMock));

        $this->om
            ->expects($this->at(0))
            ->method('getRepository')
            ->will($this->returnValue($valueRepository));

        $this->om
            ->expects($this->at(1))
            ->method('getRepository')
            ->will($this->returnValue($repository));

        $this->om
            ->expects($this->once())
            ->method('persist');
        $this->om
            ->expects($this->once())
            ->method('flush');

        $object->save($settings);
    }

    /**
     * Test getChanged
     */
    public function testGetChanged()
    {
        $settings = array(
            'oro_user___level' => array(
                'value' => 50,
            ),
        );

        $object = $this->getMock(
            'Oro\Bundle\ConfigBundle\Config\ConfigManager',
            array('get'),
            array($this->om, $this->settings)
        );

        $currentValue = array(
            'value' => 20,
            'use_parent_scope_value' => false,
        );
        $object->expects($this->once())
            ->method('get')
            ->with('oro_user.level')
            ->will($this->returnValue($currentValue));

        $object->getChanged($settings);
    }

    /**
     * @param string $configPath
     *
     * @return SystemConfigurationFormProvider
     */
    protected function getProviderWithConfigLoaded($configPath)
    {
        $config = Yaml::parse(file_get_contents($configPath));

        $processor = new ProcessorDecorator();
        $config = $processor->process($config);

        $subscriber    = $this->getMockBuilder('Oro\Bundle\ConfigBundle\Form\EventListener\ConfigSubscriber')
            ->setMethods(array('__construct'))
            ->disableOriginalConstructor()->getMock();

        $formType      = new FormType($subscriber);
        $formFieldType = new FormFieldType();

        $extensions = array(
            new PreloadedExtension(
                array(
                    $formType->getName()      => $formType,
                    $formFieldType->getName() => $formFieldType
                ),
                array()
            ),
        );

        $factory = Forms::createFormFactoryBuilder()
            ->addExtensions($extensions)
            ->addTypeExtension(
                new DataBlockExtension()
            )
            ->getFormFactory();

        $securityFacade = $this->getMockBuilder('Oro\Bundle\SecurityBundle\SecurityFacade')
                    ->disableOriginalConstructor()->getMock();

        $provider = new SystemConfigurationFormProvider($config, $factory, $securityFacade);

        return $provider;
    }
}
