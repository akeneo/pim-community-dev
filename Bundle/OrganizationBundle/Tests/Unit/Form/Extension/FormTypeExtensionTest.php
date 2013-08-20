<?php

namespace Oro\Bundle\OrganizationBundle\Tests\Form\Extension;

use Symfony\Component\Form\FormEvents;
use Oro\Bundle\OrganizationBundle\Form\Extension\FormTypeExtension;
use Oro\Bundle\OrganizationBundle\Form\Type\OwnershipType;
use Oro\Bundle\UserBundle\EventListener\RecordOwnerDataListener;

class FormTypeExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $securityContext;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $configProvider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $manager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $aclManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $config;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $builder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $user;

    private $organizations;

    private $businessUnits;

    private $fieldName;

    /**
     * @var FormTypeExtension
     */
    private $extension;

    public function setUp()
    {
        $this->securityContext = $this->getMock('Symfony\Component\Security\Core\SecurityContextInterface');

        $this->configProvider = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $this->manager = $this->getMockBuilder('Oro\Bundle\OrganizationBundle\Entity\Manager\BusinessUnitManager')
            ->disableOriginalConstructor()
            ->getMock();

        $businessUnitsTree = array(
            array(
                'id' => 1,
                'name' => 'Root',
                'children' => array(
                    array(
                        'id' => 2,
                        'name' => 'Child',
                    )
                )
            )
        );
        $this->manager->expects($this->any())
            ->method('getBusinessUnitsTree')
            ->will($this->returnValue($businessUnitsTree));

        $this->aclManager = $this->getMockBuilder('Oro\Bundle\UserBundle\Acl\Manager')
            ->disableOriginalConstructor()
            ->getMock();

        $token = $this->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\TokenInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->config = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Config\EntityConfig')
            ->disableOriginalConstructor()
            ->getMock();

        $this->configProvider->expects($this->any())
            ->method('getConfig')
            ->with('User')
            ->will($this->returnValue($this->config));

        $this->configProvider->expects($this->any())
            ->method('hasConfig')
            ->with('User')
            ->will($this->returnValue(true));

        $this->user = $this->getMockBuilder('Oro\Bundle\UserBundle\Entity\User')
            ->disableOriginalConstructor()
            ->getMock();
        $this->user->expects($this->any())->method('getId')->will($this->returnValue(1));

        $organization = $this->getMockBuilder('Oro\Bundle\OrganizationBundle\Entity\Organization')
            ->disableOriginalConstructor()
            ->getMock();
        $this->organizations = array($organization);
        $businessUnit = $this->getMockBuilder('Oro\Bundle\OrganizationBundle\Entity\BusinessUnit')
            ->disableOriginalConstructor()
            ->getMock();
        $businessUnit->expects($this->any())->method('getOrganization')->will($this->returnValue($organization));
        $this->businessUnits = array($businessUnit);
        $this->user->expects($this->any())->method('getBusinessUnits')->will($this->returnValue($this->businessUnits));

        $token->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue($this->user));

        $this->securityContext->expects($this->any())
            ->method('getToken')
            ->will($this->returnValue($token));

        $config = $this->getMockBuilder('Symfony\Component\Form\FormConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $config->expects($this->any())->method('getDataClass')->will($this->returnValue('User'));

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
        $form->expects($this->any())->method('getConfig')->will($this->returnValue($config));

        $this->builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $this->builder->expects($this->any())->method('getForm')->will($this->returnValue($form));
        $this->fieldName = RecordOwnerDataListener::OWNER_FIELD_NAME;

        $this->extension = new FormTypeExtension(
            $this->securityContext,
            $this->configProvider,
            $this->manager,
            $this->aclManager
        );
    }

    public function testGetExtendedType()
    {
        $this->assertEquals('form', $this->extension->getExtendedType());
    }

    /**
     * Testing case with user owner type and change owner permission granted
     */
    public function testUserOwnerBuildFormGranted()
    {
        $this->mockConfigs(array('is_granted' => true, 'owner_type' => OwnershipType::OWNERSHIP_TYPE_USER));
        $this->builder->expects($this->once())->method('add')->with(
            $this->fieldName,
            'oro_user_select',
            array('required' => false)
        );
        $this->extension->buildForm($this->builder, array());

    }

    /**
     * Testing case with user owner type and change owner permission isn't granted
     */
    public function testUserOwnerBuildFormNotGranted()
    {
        $this->mockConfigs(array('is_granted' => false, 'owner_type' => OwnershipType::OWNERSHIP_TYPE_USER));
        $this->builder->expects($this->never())->method('add');
        $this->extension->buildForm($this->builder, array());
    }

    /**
     * Testing case with business unit owner type and change owner permission granted
     */
    public function testBusinessUnitOwnerBuildFormGranted()
    {
        $this->mockConfigs(array('is_granted' => true, 'owner_type' => OwnershipType::OWNERSHIP_TYPE_BUSINESS_UNIT));
        $businessUnits = array(
            1 => "Root",
            2 => "&nbsp;&nbsp;&nbsp;Child"
        );
        $this->builder->expects($this->once())->method('add')->with(
            $this->fieldName,
            'oro_business_unit_tree_select',
            array(
                'choices' => $businessUnits,
                'mapped' => true,
                'required' => false,
                'attr' => array('is_safe' => true),
            )
        );
        $this->extension->buildForm($this->builder, array());
    }

    /**
     * Testing case with business unit owner type and change owner permission isn't granted
     */
    public function testBusinessUnitOwnerBuildFormNotGranted()
    {
        $this->mockConfigs(array('is_granted' => false, 'owner_type' => OwnershipType::OWNERSHIP_TYPE_BUSINESS_UNIT));
        $this->builder->expects($this->once())->method('add')->with(
            $this->fieldName,
            'entity',
            array(
                'class' => 'OroOrganizationBundle:BusinessUnit',
                'property' => 'name',
                'choices' => $this->businessUnits,
                'mapped' => true,
                'required' => false,
            )
        );
        $this->extension->buildForm($this->builder, array());
    }

    /**
     * Testing case with organization owner type and change owner permission granted
     */
    public function testOrganizationOwnerBuildFormGranted()
    {
        $this->mockConfigs(array('is_granted' => true, 'owner_type' => OwnershipType::OWNERSHIP_TYPE_ORGANIZATION));
        $this->builder->expects($this->once())->method('add')->with(
            $this->fieldName,
            'entity',
            array(
                'class' => 'OroOrganizationBundle:Organization',
                'property' => 'name',
                'mapped' => true,
                'required' => false,
            )
        );
        $this->extension->buildForm($this->builder, array());
    }

    /**
     * Testing case with organization owner type and change owner permission isn't granted
     */
    public function testOrganizationOwnerBuildFormNotGranted()
    {
        $this->mockConfigs(array('is_granted' => false, 'owner_type' => OwnershipType::OWNERSHIP_TYPE_ORGANIZATION));
        $this->builder->expects($this->once())->method('add')->with(
            $this->fieldName,
            'entity',
            array(
                'class' => 'OroOrganizationBundle:Organization',
                'property' => 'name',
                'choices' => $this->organizations,
                'mapped' => true,
                'required' => false,
            )
        );
        $this->extension->buildForm($this->builder, array());
    }

    public function testEventListener()
    {
        $this->mockConfigs(array('is_granted' => false, 'owner_type' => OwnershipType::OWNERSHIP_TYPE_ORGANIZATION));
        $this->builder->expects($this->once())->method('addEventListener')->will(
            $this->returnCallback(array($this, 'eventCallback'))
        );
        $this->extension->buildForm($this->builder, array());
    }

    public function eventCallback()
    {
        $args = func_get_args();
        $this->assertEquals($args[0], FormEvents::POST_SET_DATA);
        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
        $form->expects($this->any())->method('has')->will($this->returnValue(true));
        $form->expects($this->any())->method('get')->with($this->fieldName)->will($this->returnself());
        $form->expects($this->once())->method('remove')->with($this->fieldName);
        $form->expects($this->once())->method('add')->with(
            $this->fieldName,
            'text',
            array(
                'disabled' => true,
                'data' => '',
                'mapped' => false,
                'required' => false
            )
        );
        $formEvent = $this->getMockBuilder('Symfony\Component\Form\FormEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $formEvent->expects($this->any())->method('getForm')->will($this->returnValue($form));

        $formEvent->expects($this->any())->method('getData')->will($this->returnValue($this->user));
        call_user_func($args[1], $formEvent);
    }

    protected function mockConfigs(array $values)
    {
        $this->aclManager->expects($this->any())->method('isResourceGranted')->with('oro_change_record_owner')
                ->will($this->returnValue($values['is_granted']));

        $configs = array('owner_type' => $values['owner_type']);
        $this->config->expects($this->once())->method('getValues')->will($this->returnValue($configs));
    }
}
