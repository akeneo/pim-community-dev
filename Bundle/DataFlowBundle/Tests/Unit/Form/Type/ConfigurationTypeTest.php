<?php
namespace Oro\Bundle\DataFlowBundle\Tests\Unit\Form\Type;

use Oro\Bundle\DataFlowBundle\Tests\Unit\Form\Demo\MyConfigurationType;
use Symfony\Component\Form\FormBuilder;
use Doctrine\Tests\OrmTestCase;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;

/**
 * Test related class
 *
 *
 */
class ConfigurationTypeTest extends OrmTestCase
{

    /**
     * @var MyConfigurationType
     */
    protected $formType;

    /**
     * Setup
     */
    public function setup()
    {
        // prepare test entity manager
        $entityPath = 'Oro\\Bundle\\DataFlowBundle\\Test\\Entity\\Demo';
        $reader = new AnnotationReader();
        $metadataDriver = new AnnotationDriver($reader, $entityPath);
        $entityManager = $this->_getTestEntityManager();
        $entityManager->getConfiguration()->setMetadataDriverImpl($metadataDriver);

        $this->formType = new MyConfigurationType($entityManager);
    }

    /**
     * Test related method
     */
    public function testBuildForm()
    {
        $dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $factory = $this->getMock('Symfony\Component\Form\FormFactoryInterface');
        $builder = new FormBuilder('name', null, $dispatcher, $factory);
        $this->formType->buildForm($builder, array());
    }
}
