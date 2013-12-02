<?php

namespace Oro\Bundle\EmailBundle\Tests\Unit\Provider;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\SecurityContextInterface;

use Oro\Bundle\EmailBundle\Provider\VariablesProvider;

class VariableProviderTest extends \PHPUnit_Framework_TestCase
{
    const TEST_ENTITY_NAME = 'someEntity';
    const TEST_NOT_NEEDED_ENTITY_NAME = 'anotherEntity';

    /** @var SecurityContextInterface */
    protected $securityContext;

    /** @var \PHPUnit_Framework_MockObject_MockObject  */
    protected $configProvider;

    /** @var \PHPUnit_Framework_MockObject_MockObject  */
    protected $user;

    /** @var VariablesProvider */
    protected $provider;

    public function setUp()
    {
        $this->securityContext = $this->getMock('Symfony\Component\Security\Core\SecurityContextInterface');

        $this->configProvider = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider')
            ->disableOriginalConstructor()->getMock();
        $token = $this->getMockForAbstractClass(
            'Symfony\Component\Security\Core\Authentication\Token\TokenInterface'
        );
        $this->user = $this->getMockBuilder('Oro\Bundle\UserBundle\Entity\User')
            ->disableOriginalConstructor()->getMock();
        $token->expects($this->any())->method('getUser')
            ->will($this->returnValue($this->user));
        $this->securityContext->expects($this->any())->method('getToken')
            ->will($this->returnValue($token));

        $this->provider = new VariablesProvider($this->securityContext, $this->configProvider);
    }

    public function tearDown()
    {
        unset($this->securityContext);
        unset($this->configProvider);
        unset($this->user);
        unset($this->provider);
    }

    /**
     * @dataProvider fieldsDataProvider
     * @param $entityIsUser
     */
    public function testGetTemplateVariables($entityIsUser)
    {
        $configId1Mock = $this->getMockForAbstractClass('Oro\Bundle\EntityConfigBundle\Config\Id\ConfigIdInterface');
        $configId1Mock
            ->expects($this->once())->method('getClassName')
            ->will($this->returnValue(get_class($this->user)));
        $configId2Mock = $this->getMockForAbstractClass('Oro\Bundle\EntityConfigBundle\Config\Id\ConfigIdInterface');
        $configId2Mock
            ->expects($this->once())->method('getClassName')
            ->will($this->returnValue(self::TEST_ENTITY_NAME));
        $configId3Mock = $this->getMockForAbstractClass('Oro\Bundle\EntityConfigBundle\Config\Id\ConfigIdInterface');
        $configId3Mock
            ->expects($this->once())->method('getClassName')
            ->will($this->returnValue(self::TEST_NOT_NEEDED_ENTITY_NAME));

        $configurableEntities =  array($configId1Mock, $configId2Mock, $configId3Mock);

        $this->configProvider->expects($this->once())->method('getIds')
            ->will($this->returnValue($configurableEntities));

        $field1Id = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Config\Id\FieldConfigId')
            ->disableOriginalConstructor()
            ->getMock();
        $field1Id->expects($this->any())
            ->method('getFieldName')
            ->will($this->returnValue('someCode'));

        $field1 = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Config\ConfigInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $field2 = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Config\ConfigInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $field1->expects($this->any())
            ->method('is')
            ->with('available_in_template')
            ->will($this->returnValue(true));
        $field1->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($field1Id));

        $field2->expects($this->any())
            ->method('is')
            ->with('available_in_template')
            ->will($this->returnValue(false));

        // fields for entity
        $fieldsCollection = new ArrayCollection();
        $this->configProvider->expects($this->at(1))->method('filter')->will(
            $this->returnCallback(
                function ($callback) use ($fieldsCollection) {
                    return $fieldsCollection->filter($callback)->toArray();
                }
            )
        );
        $fieldsCollection[] = $field1;
        $fieldsCollection[] = $field2;

        if (!$entityIsUser) {
            $field3Id = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Config\Id\FieldConfigId')
                ->disableOriginalConstructor()
                ->getMock();
            $field3Id->expects($this->any())->method('getFieldName')->will($this->returnValue('someAnotherCode'));

            $field3 = clone $field1;
            $field3->expects($this->atLeastOnce())->method('is')->with('available_in_template')
                ->will($this->returnValue(true));
            $field3->expects($this->atLeastOnce())->method('getId')
                ->will($this->returnValue($field3Id));

            $this->configProvider->expects($this->at(2))->method('filter')->will(
                $this->returnCallback(
                    function ($callback) use ($fieldsCollection, $field3) {
                        $fieldsCollection[] = $field3;
                        return $fieldsCollection->filter($callback)->toArray();
                    }
                )
            );

            $result = $this->provider->getTemplateVariables(self::TEST_ENTITY_NAME);
        } else {
            $result = $this->provider->getTemplateVariables(get_class($this->user));
        }

        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('entity', $result);

        $this->assertInternalType('array', $result['user']);
        $this->assertInternalType('array', $result['entity']);

        if ($entityIsUser) {
            $this->assertEquals($result['user'], $result['entity']);
        }
    }

    /**
     * @return array
     */
    public function fieldsDataProvider()
    {
        return array(
            'entity is not user' => array(
                'entityIsUser' => false
            ),
            'entity is user' => array(
                'entityIsUser' => true
            )
        );
    }
}
