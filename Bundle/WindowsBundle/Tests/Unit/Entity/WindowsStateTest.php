<?php

namespace Oro\Bundle\WindowsBundle\Tests\Entity;

use Oro\Bundle\WindowsBundle\Entity\WindowsState;

class WindowsStateTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test getters and setters
     *
     * @dataProvider propertiesDataProvider
     * @param string $property
     * @param mixed $value
     */
    public function testGetSet($property, $value)
    {
        $state = new WindowsState();
        $setMethod = 'set' . ucfirst($property);
        $getMethod = 'get' . ucfirst($property);
        $state->$setMethod($value);
        $this->assertEquals($value, $state->$getMethod());
    }

    public function propertiesDataProvider()
    {
        $userMock = $this->getMockBuilder('Symfony\Component\Security\Core\User\UserInterface')
            ->disableOriginalConstructor()
            ->getMock();
        return array(
            'user' => array('user', $userMock),
            'data' => array('data', array('test' => true)),
            'createdAt' => array('createdAt', '2022-02-22 22:22:22'),
            'updatedAt' => array('updatedAt', '2022-02-22 22:22:22'),
        );
    }

    public function testGetJsonData()
    {
        $state = new WindowsState();
        $data = array('test' => true);
        $state->setData($data);
        $this->assertEquals($data, $state->getData());
        $this->assertEquals(json_encode($data), $state->getJsonData());
    }

    public function testDoPrePersist()
    {
        $item = new WindowsState();
        $item->doPrePersist();

        $this->assertInstanceOf('DateTime', $item->getCreatedAt());
        $this->assertInstanceOf('DateTime', $item->getUpdatedAt());
        $this->assertEquals($item->getCreatedAt(), $item->getUpdatedAt());
    }

    public function testDoPreUpdate()
    {
        $item = new WindowsState();
        $item->doPreUpdate();

        $this->assertInstanceOf('DateTime', $item->getUpdatedAt());
    }
}
