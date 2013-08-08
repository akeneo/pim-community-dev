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
        $configurableEntities =  array(
            get_class($this->user),
            self::TEST_ENTITY_NAME,
            self::TEST_NOT_NEEDED_ENTITY_NAME
        );

        $this->configProvider->expects($this->at(0))->method('getAllConfigurableEntityNames')
            ->will($this->returnValue($configurableEntities));

        $config = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Config\EntityConfig')
            ->disableOriginalConstructor()->getMock();

        $field1 = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Config\FieldConfig')
            ->disableOriginalConstructor()->getMock();
        $field2 = clone $field1;

        $field1->expects($this->atLeastOnce())->method('is')->with('available_in_template')
            ->will($this->returnValue(true));
        $field1->expects($this->atLeastOnce())->method('getCode')
            ->will($this->returnValue('someCode'));

        $field2->expects($this->atLeastOnce())->method('is')->with('available_in_template')
            ->will($this->returnValue(false));


        // fields for entity
        $fieldsCollection = new ArrayCollection();
        $config->expects($this->at(0))->method('getFields')
            ->will(
                $this->returnCallback(
                    function ($callback) use ($fieldsCollection) {
                        return $fieldsCollection->filter($callback);
                    }
                )
            );
        $fieldsCollection['someCode'] = ($field1);
        $fieldsCollection['anotherCode'] = ($field2);

        $this->configProvider->expects($this->at(1))->method('getConfig')->with(get_class($this->user))
            ->will($this->returnValue($config));

        if (!$entityIsUser) {
            $field3 = clone $field1;
            $field3->expects($this->atLeastOnce())->method('is')->with('available_in_template')
                ->will($this->returnValue(true));
            $field3->expects($this->atLeastOnce())->method('getCode')
                ->will($this->returnValue('someAnotherCode'));

            $config->expects($this->at(1))->method('getFields')
                ->will(
                    $this->returnCallback(
                        function ($callback) use ($fieldsCollection, $field3) {
                            $fieldsCollection['someAnotherCode'] = $field3;
                            return $fieldsCollection->filter($callback);
                        }
                    )
                );

            $this->configProvider->expects($this->at(2))->method('getConfig')->with(self::TEST_ENTITY_NAME)
                ->will($this->returnValue($config));
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
