<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Processor;

use Pim\Bundle\CatalogBundle\Entity\Association;
use Pim\Bundle\CatalogBundle\Entity\AssociationTranslation;

use Pim\Bundle\ImportExportBundle\Processor\ValidAssociationCreationProcessor;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ValidAssociationCreationProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->em        = $this->mock('Doctrine\ORM\EntityManager');
        $this->validator = $this->getMock('Symfony\Component\Validator\ValidatorInterface');

        $this->processor = new ValidAssociationCreationProcessor(
            $this->em,
            $this->validator
        );
        $this->stepExecution = $this->getStepExecutionMock();
        $this->processor->setStepExecution($this->stepExecution);
    }

    /**
     * Test related method
     */
    public function testGetConfigurationFields()
    {
        $this->assertEquals(array(), $this->processor->getConfigurationFields());
    }

    /**
     * Test the process method
     */
    public function testProcess()
    {
        $repository = $this->getRepositoryMock();
        $this->em
            ->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($repository));

        $associationsMap = array(
            array(array('code' => 'association_1'), null, $this->getAssociation('association_1')),
            array(array('code' => 'association_2'), null, $this->getAssociation('association_2'))
        );

        $repository
            ->expects($this->any())
            ->method('findOneBy')
            ->will($this->returnValueMap($associationsMap));

        $this->validator
            ->expects($this->any())
            ->method('validate')
            ->will($this->returnValue($this->getConstraintViolationListMock()));

        $data = $this->getValidAssociationData();

        $this->assertEquals($data['result'], $this->processor->process($data['csv']));
    }

    /**
     * @return array
     */
    protected function getValidAssociationData()
    {
        return array(
            'csv' => array(
                $this->getRow('association_1'),
                $this->getRow('association_2')
            ),
            'result' => array(
                $this->getAssociation('association_1'),
                $this->getAssociation('association_2')
            )
        );
    }

    public function testInvalidProcess()
    {
        $repository = $this->getRepositoryMock();
        $this->em
            ->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($repository));

        $associationsMap = array(
            array(array('code' => 'association_1'), null, $this->getAssociation('association_1')),
            array(array('code' => 'association_2'), null, $this->getAssociation('association_2'))
        );

        $repository
            ->expects($this->any())
            ->method('findOneBy')
            ->will($this->returnValueMap($associationsMap));

        $withViolations    = $this->getConstraintViolationListMock(array('The foo error message'));
        $withoutViolations = $this->getConstraintViolationListMock();

        $this->stepExecution
            ->expects($this->exactly(1))
            ->method('addError')
            ->with('The foo error message');

        $this->validator
            ->expects($this->any())
            ->method('validate')
            ->will(
                $this->returnCallback(
                    function ($object) use ($withViolations, $withoutViolations) {
                        if ($object->getCode() === 'association_1') {
                            return $withoutViolations;
                        }

                        return $withViolations;
                    }
                )
            );

        $data = $this->getInvalidAssociationData();
        $this->assertEquals($data['result'], $this->processor->process($data['csv']));
    }

    protected function getInvalidAssociationData()
    {
        return array(
            'csv' => array(
                $this->getRow('association_1'),
                $this->getRow('association_2')
            ),
            'result' => array(
                $this->getAssociation('association_1')
            )
        );
    }

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
     * @return EntityRepository
     */
    protected function getRepositoryMock()
    {
        return $this->mock('Doctrine\ORM\EntityRepository');
    }

    /**
     * Create an array representing a single CSV row
     *
     * @param string $code
     *
     * @return array
     */
    protected function getRow($code)
    {
        return array(
            'code'        => $code,
            'label-en_US' => sprintf('%s (en)', ucfirst($code)),
            'label-fr_FR' => sprintf('%s (fr)', ucfirst($code)),
        );
    }

    /**
     * @param string $code
     *
     * @return Association
     */
    protected function getAssociation($code)
    {
        $association = new Association();
        $association->setCode($code);

        $english = new AssociationTranslation();
        $english->setLocale('en_US');
        $english->setLabel(ucfirst($code) .' (en)');
        $association->addTranslation($english);

        $french = new AssociationTranslation();
        $french->setLocale('fr_FR');
        $french->setLabel(ucfirst($code) .' (fr)');
        $association->addTranslation($french);

        return $association;
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
     * @return \Oro\Bundle\BatchBundle\Entity\StepExecution
     */
    protected function getStepExecutionMock()
    {
        return $this
            ->getMockBuilder('Oro\Bundle\BatchBundle\Entity\StepExecution')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
