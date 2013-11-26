<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Processor;

/**
 * Abstract for validation processor test
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @abstract
 */
abstract class AbstractProcessorTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Symfony\Component\Validator\ValidatorInterface
     */
    protected $validator;

    /**
     * @var AbstractConfigurableStepElement
     */
    protected $processor;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->em        = $this->mock('Doctrine\ORM\EntityManager');
        $this->validator = $this->getMock('Symfony\Component\Validator\ValidatorInterface');

        $this->processor = $this->createProcessor();
    }

    /**
     * Create processor
     *
     * @return AbstractConfigurableStepElement
     *
     * @abstract
     */
    abstract protected function createProcessor();

    /**
     * @param string $class
     *
     * @return mixed
     */
    protected function mock($class)
    {
        return $this->getMockBuilder($class)->disableOriginalConstructor()->getMock();
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getRepositoryMock()
    {
        return $this->mock('Doctrine\ORM\EntityRepository');
    }

    /**
     * @param array $violations
     *
     * @return \Symfony\Component\Validator\ConstraintViolationList
     */
    protected function getConstraintViolationListMock(array $violations = array())
    {
        $list = $this->getMock('Symfony\Component\Validator\ConstraintViolationList');

        $list
            ->expects($this->any())
            ->method('count')
            ->will($this->returnValue(count($violations)));

        $list
            ->expects($this->any())
            ->method('getIterator')
            ->will($this->returnValue(new \ArrayIterator($violations)));

        return $list;
    }

    /**
     * Test related method
     */
    public function testGetConfigurationFields()
    {
        $this->assertEquals(
            $this->getExpectedConfigurationFields(),
            $this->processor->getConfigurationFields()
        );
    }

    /**
     * Get expected configuration fields
     *
     * @return array
     *
     * @abstract
     */
    abstract protected function getExpectedConfigurationFields();
}
