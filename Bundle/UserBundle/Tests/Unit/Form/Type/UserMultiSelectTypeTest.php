<?php
namespace Oro\Bundle\UserBundle\Tests\Unit\Type;

use Oro\Bundle\UserBundle\Form\Type\UserMultiSelectType;
use Symfony\Component\Form\FormEvents;

class UserMultiSelectTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UserMultiSelectType
     */
    protected $type;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $em;

    /**
     * Setup test env
     */
    public function setUp()
    {
        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->type = new UserMultiSelectType($this->em);
    }

    public function testBuildView()
    {
        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $metadata = $this->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();

        $this->em->expects($this->once())
            ->method('getClassMetadata')
            ->will($this->returnValue($metadata));

        $metadata->expects($this->once())
            ->method('getSingleIdentifierFieldName')
            ->will($this->returnValue('id'));

        $phpUnit = $this;
        $builder->expects($this->once())
            ->method('addEventListener')
            ->with(FormEvents::PRE_SUBMIT, $this->isInstanceOf('\Closure'))
            ->will(
                $this->returnCallback(
                    function ($event, $callback) use ($phpUnit) {
                        $eventMock = $phpUnit->getMockBuilder('Symfony\Component\Form\FormEvent')
                            ->disableOriginalConstructor()
                            ->getMock();
                        $eventMock->expects($phpUnit->once())
                            ->method('getData')
                            ->will($phpUnit->returnValue(''));
                        $eventMock->expects($phpUnit->once())
                            ->method('setData')
                            ->with($phpUnit->equalTo(array()));

                        $callback($eventMock);
                    }
                )
            );

        $builder->expects($this->once())
            ->method('addModelTransformer')
            ->with($this->isInstanceOf('Oro\Bundle\FormBundle\Form\DataTransformer\EntitiesToIdsTransformer'));

        $this->type->buildForm($builder, array('entity_class' => 'Oro\Bundle\UserBundle\Entity\User'));
    }

    public function testSetDefaultOptions()
    {
        $resolver = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolverInterface');
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with($this->isType('array'));
        $this->type->setDefaultOptions($resolver);
    }

    public function testGetParent()
    {
        $this->assertEquals('oro_jqueryselect2_hidden', $this->type->getParent());
    }

    public function testGetName()
    {
        $this->assertEquals('oro_user_multiselect', $this->type->getName());
    }
}
