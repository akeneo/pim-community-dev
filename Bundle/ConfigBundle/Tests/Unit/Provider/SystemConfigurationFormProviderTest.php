<?php
namespace ConfigBundle\Tests\Provider;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\FormIntegrationTestCase;

use Oro\Bundle\ConfigBundle\Form\Type\FormFieldType;
use Oro\Bundle\ConfigBundle\Form\Type\FormType;
use Oro\Bundle\FormBundle\Form\Extension\DataBlockExtension;
use Oro\Bundle\ConfigBundle\Provider\SystemConfigurationFormProvider;
use Oro\Bundle\ConfigBundle\DependencyInjection\SystemConfiguration\ProcessorDecorator;

class SystemConfigurationFormProviderTest extends FormIntegrationTestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $securityFacade;

    public function setUp()
    {
        parent::setUp();

        $this->factory = Forms::createFormFactoryBuilder()
            ->addExtensions($this->getExtensions())
            ->addTypeExtension(
                new DataBlockExtension()
            )
            ->getFormFactory();

        $this->securityFacade = $this->getMockBuilder('Oro\Bundle\SecurityBundle\SecurityFacade')
            ->disableOriginalConstructor()->getMock();
    }

    public function tearDown()
    {
        parent::tearDown();
        unset($this->securityFacade);
    }

    public function testTreeProcessing()
    {
        // check good_definition.yml for further details
        $provider = $this->getProviderWithConfigLoaded(__DIR__ . '/../Fixtures/Provider/good_definition.yml');
        $form = $provider->getForm('third_group');
        $this->assertInstanceOf('Symfony\Component\Form\FormInterface', $form);

        // test that fields were added
        $this->assertTrue($form->has('some_field'));
        $this->assertTrue($form->has('some_another_field'));

        // only needed fields were added
        $this->assertCount(2, $form);
    }

    /**
     * @dataProvider exceptionDataProvider
     */
    public function testExceptions($filename, $message, $method, $arguments)
    {
        $this->setExpectedException('\Exception', $message);
        $provider = $this->getProviderWithConfigLoaded(__DIR__ . '/../Fixtures/Provider/' . $filename);
        call_user_func_array(array($provider, $method), $arguments);
    }

    /**
     * @return array
     */
    public function exceptionDataProvider()
    {
        return array(
            'tree does not defined should trigger error' => array(
                'filename'  => 'tree_does_not_defined.yml',
                'message'   => 'Tree "system_configuration" does not defined',
                'method'    => 'getTree',
                'arguments' => array()
            ),
            'fields definition on bad tree level'        => array(
                'filename'  => 'bad_field_level_definition.yml',
                'message'   => 'Field "some_field" will not be ever rendered. Please check nesting level',
                'method'    => 'getTree',
                'arguments' => array()
            ),
            'trying to get not existing subtree'         => array(
                'filename'  => 'good_definition.yml',
                'message'   => 'Subtree "NOT_EXISTING_ONE" not found',
                'method'    => 'getSubtree',
                'arguments' => array('NOT_EXISTING_ONE')
            ),
            'bad field definition'                       => array(
                'filename'  => 'bad_field_definition.yml',
                'message'   => 'Field "NOT_EXISTED_FIELD" does not defined',
                'method'    => 'getTree',
                'arguments' => array()
            ),
            'bad group definition'                       => array(
                'filename'  => 'bad_group_definition.yml',
                'message'   => 'Group "NOT_EXITED_GROUP" does not defined',
                'method'    => 'getTree',
                'arguments' => array()
            ),
        );
    }

    public function testTreeProcessingWithACL()
    {
        // check good_definition_with_acl_check.yml for further details
        $provider = $this->getProviderWithConfigLoaded(
            __DIR__ . '/../Fixtures/Provider/good_definition_with_acl_check.yml'
        );

        $this->securityFacade->expects($this->at(0))->method('isGranted')->with($this->equalTo('ALLOWED'))
            ->will($this->returnValue(true));
        $this->securityFacade->expects($this->at(1))->method('isGranted')->with($this->equalTo('DENIED'))
            ->will($this->returnValue(false));

        $form = $provider->getForm('third_group');
        $this->assertInstanceOf('Symfony\Component\Form\FormInterface', $form);

        // test that fields were added
        $this->assertTrue($form->has('some_field'));
        $this->assertFalse($form->has('some_another_field'));

        // only needed fields were added
        $this->assertCount(1, $form);
    }

    /**
     * @dataProvider activeGroupsDataProvider
     *
     * @param string $activeGroup
     * @param string $activeSubGroup
     * @param string $expectedGroup
     * @param string $expectedSubGroup
     */
    public function testChooseActiveGroups($activeGroup, $activeSubGroup, $expectedGroup, $expectedSubGroup)
    {
        $provider = $this->getProviderWithConfigLoaded(__DIR__ . '/../Fixtures/Provider/good_definition.yml');
        list($activeGroup, $activeSubGroup) = $provider->chooseActiveGroups($activeGroup, $activeSubGroup);
        $this->assertEquals($expectedGroup, $activeGroup);
        $this->assertEquals($expectedSubGroup, $activeSubGroup);
    }

    public function activeGroupsDataProvider()
    {
        return array(
            'check auto choosing both groups' => array(
                null,
                null,
                'horizontal tab name' => 'first_group',
                'vertical tab name'   => 'third_group'
            ),
            'check auto choosing sub group' => array(
                'first_group',
                null,
                'horizontal tab name' => 'first_group',
                'vertical tab name'   => 'third_group'
            ),
            'check not changing if all exists' => array(
                'first_group',
                'another_branch_first',
                'horizontal tab name' => 'first_group',
                'vertical tab name'   => 'another_branch_first'
            )
        );
    }

    /**
     * Parse config fixture and validate through processorDecorator
     *
     * @param string $path
     *
     * @return array
     */
    protected function getConfig($path)
    {
        $config = Yaml::parse(file_get_contents($path));

        $processor = new ProcessorDecorator();

        return $processor->process($config);
    }

    /**
     * @param string $configPath
     *
     * @return SystemConfigurationFormProvider
     */
    protected function getProviderWithConfigLoaded($configPath)
    {
        $config = $this->getConfig($configPath);
        $provider = new SystemConfigurationFormProvider($config, $this->factory, $this->securityFacade);

        return $provider;
    }

    public function getExtensions()
    {
        $subscriber    = $this->getMockBuilder('Oro\Bundle\ConfigBundle\Form\EventListener\ConfigSubscriber')
            ->setMethods(array('__construct'))
            ->disableOriginalConstructor()->getMock();

        $formType      = new FormType($subscriber);
        $formFieldType = new FormFieldType();

        return array(
            new PreloadedExtension(
                array(
                    $formType->getName()      => $formType,
                    $formFieldType->getName() => $formFieldType
                ),
                array()
            ),
        );
    }
}
