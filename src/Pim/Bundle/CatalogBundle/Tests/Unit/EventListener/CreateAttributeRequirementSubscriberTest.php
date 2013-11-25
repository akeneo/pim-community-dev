<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\EventListener;

use Pim\Bundle\CatalogBundle\EventListener\CreateAttributeRequirementSubscriber;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateAttributeRequirementSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->factory    = $this->getAttributeRequirementFactoryMock();
        $this->subscriber = new CreateAttributeRequirementSubscriber($this->factory);
    }

    /**
     * Test related method
     */
    public function testSubscribedEvents()
    {
        $this->assertEquals(array('prePersist'), $this->subscriber->getSubscribedEvents());
    }

    /**
     * Test related method
     */
    public function testPrePersist()
    {
        $channel     = $this->getChannelMock();

        $sku         = $this->getProductAttributeMock('pim_catalog_identifier');
        $name        = $this->getProductAttributeMock('pim_catalog_text');
        $description = $this->getProductAttributeMock('pim_catalog_text');

        $family1     = $this->getFamilyMock(array($sku, $name, $description));
        $family2     = $this->getFamilyMock(array($sku, $name));

        $em          = $this->getEntityManagerMock(array($family1, $family2));
        $event       = $this->getEventMock($channel, $em);

        $requirement1 = $this->getAttributeRequirementMock();
        $requirement2 = $this->getAttributeRequirementMock();
        $requirement3 = $this->getAttributeRequirementMock();
        $requirement4 = $this->getAttributeRequirementMock();
        $requirement5 = $this->getAttributeRequirementMock();

        $requirement1->expects($this->once())->method('setFamily')->with($family1);
        $requirement2->expects($this->once())->method('setFamily')->with($family1);
        $requirement3->expects($this->once())->method('setFamily')->with($family1);
        $requirement4->expects($this->once())->method('setFamily')->with($family2);
        $requirement5->expects($this->once())->method('setFamily')->with($family2);

        $this->factory
            ->expects($this->at(0))
            ->method('createAttributeRequirement')
            ->with($sku, $channel, true)
            ->will($this->returnValue($requirement1));

        $this->factory
            ->expects($this->at(1))
            ->method('createAttributeRequirement')
            ->with($name, $channel, false)
            ->will($this->returnValue($requirement2));

        $this->factory
            ->expects($this->at(2))
            ->method('createAttributeRequirement')
            ->with($description, $channel, false)
            ->will($this->returnValue($requirement3));

        $this->factory
            ->expects($this->at(3))
            ->method('createAttributeRequirement')
            ->with($sku, $channel, true)
            ->will($this->returnValue($requirement4));

        $this->factory
            ->expects($this->at(4))
            ->method('createAttributeRequirement')
            ->with($name, $channel, false)
            ->will($this->returnValue($requirement5));

        $em->expects($this->at(1))->method('persist')->with($requirement1);
        $em->expects($this->at(2))->method('persist')->with($requirement2);
        $em->expects($this->at(3))->method('persist')->with($requirement3);
        $em->expects($this->at(4))->method('persist')->with($requirement4);
        $em->expects($this->at(5))->method('persist')->with($requirement5);

        $this->subscriber->prePersist($event);
    }

    /**
     * Test related method
     */
    public function testIgnoreNonChannelEntity()
    {
        $em    = $this->getEntityManagerMock(array());
        $event = $this->getEventMock($this->getMock('\stdClass'), $em);

        $event->expects($this->never())->method('getEntityManager');

        $this->subscriber->prePersist($event);
    }

    /**
     * @return \Pim\Bundle\CatalogBundle\Factory\AttributeRequirementFactory
     */
    protected function getAttributeRequirementFactoryMock()
    {
        return $this->getMock('Pim\Bundle\CatalogBundle\Factory\AttributeRequirementFactory');
    }

    /**
     * @param array $families
     *
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManagerMock($families)
    {
        $em = $this
            ->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $em->expects($this->any())
            ->method('getRepository')
            ->will(
                $this->returnValue(
                    $this->getFamilyRepositoryMock(
                        $families
                    )
                )
            );

        return $em;
    }

    /**
     * @return \Pim\Bundle\CatalogBundle\Entity\Channel
     */
    protected function getChannelMock()
    {
        return $this->getMock('Pim\Bundle\CatalogBundle\Entity\Channel');
    }

    /**
     * @param array $attributes
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\Family
     */
    protected function getFamilyMock(array $attributes)
    {
        $family = $this->getMock('Pim\Bundle\CatalogBundle\Entity\Family');

        $family->expects($this->any())
            ->method('getAttributes')
            ->will($this->returnValue($attributes));

        return $family;
    }

    /**
     * @param array $families
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getFamilyRepositoryMock(array $families)
    {
        $repository = $this
            ->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $repository->expects($this->any())
            ->method('findAll')
            ->will($this->returnValue($families));

        return $repository;
    }

    /**
     * @param object $entity
     * @param object $em
     *
     * @return \Doctrine\ORM\Event\LifecycleEventArgs
     */
    protected function getEventMock($entity, $em)
    {
        $event = $this
            ->getMockBuilder('Doctrine\ORM\Event\LifecycleEventArgs')
            ->disableOriginalConstructor()
            ->getMock();

        $event->expects($this->any())
            ->method('getEntity')
            ->will($this->returnValue($entity));

        $event->expects($this->any())
            ->method('getEntityManager')
            ->will($this->returnValue($em));

        return $event;
    }

    /**
     * @param string $attributeType
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\ProductAttribute
     */
    protected function getProductAttributeMock($attributeType)
    {
        $attribute = $this->getMock('Pim\Bundle\CatalogBundle\Entity\ProductAttribute');

        $attribute->expects($this->any())
            ->method('getAttributeType')
            ->will($this->returnValue($attributeType));

        return $attribute;
    }

    /**
     * @return \Pim\Bundle\CatalogBundle\Entity\AttributeRequirement
     */
    protected function getAttributeRequirementMock()
    {
        return $this->getMock('Pim\Bundle\CatalogBundle\Entity\AttributeRequirement');
    }
}
