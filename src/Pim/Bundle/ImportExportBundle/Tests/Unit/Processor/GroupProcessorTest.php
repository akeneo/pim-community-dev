<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Processor;

use Oro\Bundle\BatchBundle\Item\InvalidItemException;
use Pim\Bundle\CatalogBundle\Model\Group;
use Pim\Bundle\CatalogBundle\Model\GroupTranslation;
use Pim\Bundle\ImportExportBundle\Processor\GroupProcessor;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupProcessorTest extends AbstractProcessorTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function createProcessor()
    {
        return new GroupProcessor($this->em, $this->validator);
    }

    /**
     * {@inheritdoc}
     */
    protected function getExpectedConfigurationFields()
    {
        return array();
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

        $groupsMap = array(
            array(array('code' => 'group_1'), null, $this->getGroup('group_1')),
            array(array('code' => 'group_2'), null, $this->getGroup('group_2'))
        );

        $repository
            ->expects($this->any())
            ->method('findOneBy')
            ->will($this->returnValueMap($groupsMap));

        $this->validator
            ->expects($this->any())
            ->method('validate')
            ->will($this->returnValue($this->getconstraintViolationListMock()));

        $this->assertEquals(
            $this->getGroup('group_1'),
            $this->processor->process($this->getRow('group_1'))
        );
        $this->assertEquals(
            $this->getGroup('group_2'),
            $this->processor->process($this->getRow('group_2'))
        );
    }

    /**
     * Test the process method returning validation errors
     * @expectedException Oro\Bundle\BatchBundle\Item\InvalidItemException
     */
    public function testInvalidProcess()
    {
        $repository = $this->getRepositoryMock();
        $this->em
            ->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($repository));

        $groupsMap = array(
            array(array('code' => 'group_1'), null, $this->getGroup('group_1')),
            array(array('code' => 'group_2'), null, $this->getGroup('group_2'))
        );

        $repository
            ->expects($this->any())
            ->method('findOneBy')
            ->will($this->returnValueMap($groupsMap));

        $withViolations    = $this->getConstraintViolationListMock(array('The foo error message'));
        $withoutViolations = $this->getConstraintViolationListMock();

        $this->validator
            ->expects($this->any())
            ->method('validate')
            ->will(
                $this->returnCallback(
                    function ($object) use ($withViolations, $withoutViolations) {
                        if ($object->getCode() === 'group_1') {
                            return $withoutViolations;
                        }

                        return $withViolations;
                    }
                )
            );

        $this->assertEquals(
            $this->getGroup('group_1'),
            $this->processor->process($this->getRow('group_1'))
        );
        $this->assertEquals(
            $this->getGroup('group_2'),
            $this->processor->process($this->getRow('group_2'))
        );
    }

    /**
     * @return array
     */
    protected function getInvalidGroupData()
    {
        return array(
            'csv' => array(
                $this->getRow('group_1'),
                $this->getRow('group_2')
            ),
            'result' => array(
                $this->getGroup('group_1')
            )
        );
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
            'label-fr_FR' => sprintf('%s (fr)', ucfirst($code))
        );
    }

    /**
     * Create a group entity from properties
     *
     * @param string $code
     *
     * @return Group
     */
    protected function getGroup($code)
    {
        $group = new Group();
        $group->setCode($code);

        $english = new GroupTranslation();
        $english->setLocale('en_US');
        $english->setLabel(ucfirst($code) .' (en)');
        $group->addTranslation($english);

        $french = new GroupTranslation();
        $french->setLocale('fr_FR');
        $french->setLabel(ucfirst($code) .' (fr)');
        $group->addTranslation($french);

        return $group;
    }
}
