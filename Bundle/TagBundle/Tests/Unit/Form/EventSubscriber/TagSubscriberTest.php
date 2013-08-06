<?php

namespace Oro\Bundle\TagBundle\Tests\Unit\Form\EventSubscriber;

use Symfony\Component\Form\FormEvents;

use Oro\Bundle\TagBundle\Entity\Tag;
use Oro\Bundle\TagBundle\Form\EventSubscriber\TagSubscriber;

class TagSubscriberTest extends \PHPUnit_Framework_TestCase
{
    const TEST_TAG_NAME = 'testName';

    /** @var TagSubscriber */
    protected $subscriber;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    protected $manager;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    protected $transformer;

    public function setUp()
    {
        $this->transformer = $this->getMockBuilder('Oro\Bundle\TagBundle\Form\Transformer\TagTransformer')
            ->disableOriginalConstructor()->getMock();
        $this->manager = $this->getMockBuilder('Oro\Bundle\TagBundle\Entity\TagManager')
            ->disableOriginalConstructor()->getMock();

        $this->subscriber = new TagSubscriber($this->manager, $this->transformer);
    }

    public function tearDown()
    {
        unset($this->subscriber);
        unset($this->transformer);
        unset($this->manager);
    }

    public function testSubscribedEvents()
    {
        $result = TagSubscriber::getSubscribedEvents();

        $this->assertArrayHasKey(FormEvents::PRE_SET_DATA, $result);
        $this->assertArrayHasKey(FormEvents::PRE_SUBMIT, $result);

        $this->assertEquals('preSet', $result[FormEvents::PRE_SET_DATA]);
        $this->assertEquals('preSubmit', $result[FormEvents::PRE_SUBMIT]);
    }

    /**
     * @dataProvider entityProvider
     */
    public function testPreSet($entity, $shouldSetData)
    {
        $eventMock = $this->getMockBuilder('Symfony\Component\Form\FormEvent')
            ->disableOriginalConstructor()->getMock();

        $parentFormMock = $this->getMockForAbstractClass('Symfony\Component\Form\Test\FormInterface');
        $parentFormMock->expects($this->once())->method('getData')
            ->will($this->returnValue($entity));

        $formMock = $this->getMockForAbstractClass('Symfony\Component\Form\Test\FormInterface');
        $formMock->expects($this->once())->method('getParent')
            ->will($this->returnValue($parentFormMock));

        $eventMock->expects($this->once())->method('getForm')
            ->will($this->returnValue($formMock));

        if ($shouldSetData) {
            $this->manager->expects($this->once())->method('getPreparedArray')
                ->with($entity)->will(
                    $this->returnValue(
                        array(array('owner' => false), array('owner' => true))
                    )
                );

            $this->transformer->expects($this->exactly($shouldSetData))->method('setEntity')->with($entity);
            $eventMock->expects($this->exactly($shouldSetData))->method('setData');
        } else {
            $this->manager->expects($this->never())->method('getPreparedArray');
            $this->transformer->expects($this->never())->method('setEntity');
            $eventMock->expects($this->never())->method('setData');
        }



        $this->subscriber->preSet($eventMock);
    }

    /**
     * @return array
     */
    public function entityProvider()
    {
        return array(
            'instance of taggable' => array($this->getMock('Oro\Bundle\TagBundle\Entity\Taggable'), 1),
            'another entity'       => array($this->getMock('Oro\Bundle\TagBundle\Tests\Unit\Fixtures\Entity'), false),
        );
    }

    /**
     * @dataProvider submittedData
     * @param $data
     */
    public function testPreSubmit($data)
    {
        $eventMock = $this->getMockBuilder('Symfony\Component\Form\FormEvent')
            ->disableOriginalConstructor()->getMock();
        $eventMock->expects($this->once())->method('getData')
            ->will($this->returnValue($data));

        $this->manager->expects($this->once())->method('loadOrCreateTags')
            ->with(
                array(
                    self::TEST_TAG_NAME
                )
            )->will($this->returnValue(array(new Tag(self::TEST_TAG_NAME))));

        $phpUnit = $this;
        $eventMock->expects($this->once())->method('setData')
            ->will(
                $this->returnCallback(
                    function ($entities) use ($phpUnit) {
                        $phpUnit->assertArrayHasKey('all', $entities);
                        $phpUnit->assertArrayHasKey('owner', $entities);

                        $phpUnit->assertContainsOnlyInstancesOf('Oro\Bundle\TagBundle\Entity\Tag', $entities['all']);
                        $phpUnit->assertEmpty($entities['owner']);
                    }
                )
            );
        $this->subscriber->preSubmit($eventMock);
    }

    public function submittedData()
    {
        return array(
            'json submitted data' => array(
                array(
                    'all'   => "[{\"name\":\"" . self::TEST_TAG_NAME . "\"}]",
                    'owner' => '[incorrect JSON]'
                )
            )
        );
    }
}
