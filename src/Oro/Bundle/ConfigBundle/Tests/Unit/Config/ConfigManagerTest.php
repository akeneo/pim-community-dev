<?php

namespace Oro\Bundle\ConfigBundle\Tests\Unit\Config;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\ConfigBundle\DependencyInjection\SystemConfiguration\ProcessorDecorator;
use Oro\Bundle\ConfigBundle\Entity\Config;
use Oro\Bundle\ConfigBundle\Entity\ConfigValue;
use Oro\Bundle\ConfigBundle\Entity\Repository\ConfigRepository;
use Oro\Bundle\ConfigBundle\Entity\Repository\ConfigValueRepository;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Yaml\Yaml;

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
    protected $settings = [
        'pim_user' => [
            'greeting' => [
                'value' => true,
                'type'  => 'boolean',
            ],
            'level'    => [
                'value' => 20,
                'type'  => 'scalar',
            ]
        ],
        'oro_test' => [
            'anysetting' => [
                'value' => 'anyvalue',
                'type'  => 'scalar',
            ],
        ],
    ];

    /**
     * @var array
     */
    protected $loadedSettings = [
        'pim_user' => [
            'level'    => [
                'value' => 2000,
                'type'  => 'scalar',
            ]
        ],
    ];

    protected function setUp(): void
    {
        if (!interface_exists('Doctrine\Common\Persistence\ObjectManager')) {
            $this->markTestSkipped('Doctrine Common has to be installed for this test to run.');
        }

        $this->om = $this->createMock('Doctrine\Common\Persistence\ObjectManager');
        $this->object = new ConfigManager($this->om, $this->settings);
    }

    /**
     * Test get loaded settings
     */
    public function testGetLoaded()
    {
        $loadedSettings = $this->loadedSettings;

        $repository = $this->getMockBuilder(ConfigRepository::class)
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

        $this->assertEquals($this->loadedSettings['pim_user']['level']['value'], $object->get('pim_user.level'));
        $this->assertEquals($this->settings['pim_user']['greeting']['value'], $object->get('pim_user.greeting'));

        $this->assertNull($object->get('oro_test.nosetting'));
        $this->assertNull($object->get('noservice.nosetting'));
    }

    /**
     * Test default settings condition
     */
    public function testGetDefaultSettings()
    {
        $object = $this->createMock(
            ConfigManager::class,
            ['loadStoredSettings'],
            [$this->om, $this->settings]
        );

        $this->assertEquals($this->settings['pim_user']['greeting']['value'], $object->get('pim_user.greeting', true));
        $this->assertEquals($this->settings['pim_user']['level']['value'], $object->get('pim_user.level', true));
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
        $object = $this->createMock(
            ConfigManager::class,
            ['get'],
            [$this->om, $this->settings]
        );

        $testSetting = [
            'value' => 'test',
        ];

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
        $settings = [
            'pim_user___level' => [
                'value' => 50,
            ],
        ];

        $removed = [
            'pim_user___greeting' => [
                'value' => 'new value',
            ],
        ];

        $object = $this->createMock(
            ConfigManager::class,
            ['getChanged'],
            [$this->om, $this->settings]
        );

        $changes = [
            $settings, $removed
        ];

        $object->expects($this->once())
            ->method('getChanged')
            ->with($this->equalTo($settings))
            ->will($this->returnValue($changes));

        $configMock = $this->createMock(Config::class);
        $configMock->expects($this->once())
            ->method('getOrCreateValue')
            ->will($this->returnValue(new ConfigValue()));
        $configMock->expects($this->once())
            ->method('getValues')
            ->will($this->returnValue(new ArrayCollection()));

        $valueRepository = $this->getMockBuilder(ConfigValueRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repository = $this->getMockBuilder(ConfigRepository::class)
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
        $settings = [
            'pim_user___level' => [
                'value' => 50,
            ],
        ];

        $object = $this->createMock(
            ConfigManager::class,
            ['get'],
            [$this->om, $this->settings]
        );

        $currentValue = [
            'value'                  => 20,
            'use_parent_scope_value' => false,
        ];
        $object->expects($this->once())
            ->method('get')
            ->with('pim_user.level')
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

        $subscriber = $this->getMockBuilder('Oro\Bundle\ConfigBundle\Form\EventListener\ConfigSubscriber')
            ->setMethods(['__construct'])
            ->disableOriginalConstructor()->getMock();

        $formType = new FormType($subscriber);
        $formFieldType = new FormFieldType();

        $extensions = [
            new PreloadedExtension(
                [
                    $formType->getName()      => $formType,
                    $formFieldType->getName() => $formFieldType
                ],
                []
            ),
        ];

        $factory = Forms::createFormFactoryBuilder()
            ->addExtensions($extensions)
            ->getFormFactory();

        $securityFacade = $this->getMockBuilder(SecurityFacade::class)
                    ->disableOriginalConstructor()->getMock();

        $provider = new SystemConfigurationFormProvider($config, $factory, $securityFacade);

        return $provider;
    }
}
