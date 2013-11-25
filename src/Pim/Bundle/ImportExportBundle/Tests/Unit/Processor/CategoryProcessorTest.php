<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Processor;

use Oro\Bundle\BatchBundle\Item\InvalidItemException;
use Pim\Bundle\ImportExportBundle\Processor\CategoryProcessor;
use Pim\Bundle\CatalogBundle\Entity\Category;
use Pim\Bundle\CatalogBundle\Entity\CategoryTranslation;

/**
 * Test related class
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryProcessorTest extends AbstractProcessorTestCase
{
    /**
     * {@inheritdoc}
     */
    public function createProcessor()
    {
        return new CategoryProcessor($this->em, $this->validator);
    }

    /**
     * Test getter/setter for circularRefsChecked
     */
    public function testGetIsCircularRefsChecked()
    {
        $this->assertEquals(true, $this->processor->isCircularRefsChecked());
        $checkRefs = false;
        $this->processor->setCircularRefsChecked($checkRefs);
        $this->assertEquals($checkRefs, $this->processor->isCircularRefsChecked());
    }

    /**
     * Test getConfigurationFields method
     * @return array
     */
    protected function getExpectedConfigurationFields()
    {
        return array(
            'circularRefsChecked' => array(
                'type' => 'switch',
            ),
        );
    }

    /**
     * Test the process method
     */
    public function testProcess()
    {
        $repository = $this->getRepositoryMock();
        $this->em->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($repository));

        $categoriesMap = array(
            array(array('code' => 'root'), null, $this->getCategory('root')),
            array(array('code' => 'child1'), null, $this->getCategory('child1')),
            array(array('code' => 'child2'), null, $this->getCategory('child2')),
            array(array('code' => 'child3'), null, $this->getCategory('child3')),
        );

        $repository->expects($this->any())
            ->method('findOneBy')
            ->will($this->returnValueMap($categoriesMap));

        $this->validator
            ->expects($this->any())
            ->method('validate')
            ->will($this->returnValue($this->getConstraintViolationListMock()));

        $data = $this->getValidCategoryData();
        $this->assertEquals($data['result'], $this->processor->process($data['csv']));
    }

    /**
     * @return array
     */
    protected function getValidCategoryData()
    {
        return array(
            'csv' => array(
                $this->getRow('root'),
                $this->getRow('child1', 'root'),
                $this->getRow('child2', 'root'),
                $this->getRow('child3', 'root'),
            ),
            'result' => array(
                $this->getCategory('root'),
                $this->getCategory('child1', 'root'),
                $this->getCategory('child2', 'root'),
                $this->getCategory('child3', 'root'),
            )
        );
    }

    /**
     * Test related method
     *
     * @expectedException Oro\Bundle\BatchBundle\Item\InvalidItemException
     *
     * @return null
     */
    public function testInvalidProcess()
    {
        $repository = $this->getRepositoryMock();
        $this->em->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($repository));

        $categoriesMap = array(
            array(array('code' => 'child1'), null, $this->getCategory('child1')),
            array(array('code' => 'child2'), null, $this->getCategory('child2')),
            array(array('code' => 'child3'), null, $this->getCategory('child3')),
        );

        $repository->expects($this->any())
            ->method('findOneBy')
            ->will($this->returnValueMap($categoriesMap));

        $withViolations    = $this->getConstraintViolationListMock(array('The foo error message'));
        $withoutViolations = $this->getConstraintViolationListMock();

        $this->validator
            ->expects($this->any())
            ->method('validate')
            ->will(
                $this->returnCallback(
                    function ($object) use ($withViolations, $withoutViolations) {
                        if ('root' === $object->getCode()) {
                            return $withoutViolations;
                        }

                        return $withViolations;
                    }
                )
            );

        $data = $this->getInvalidCategoryData();
        $this->assertEquals($data['result'], $this->processor->process($data['csv']));
    }

    /**
     * @return array
     */
    protected function getInvalidCategoryData()
    {
        return array(
            'csv' => array(
                $this->getRow('root'),
                $this->getRow('child1', 'root'),
                $this->getRow('child2', 'root'),
                $this->getRow('child3', 'child3'),
            ),
            'result' => array(
                $this->getCategory('root'),
            )
        );
    }

    /**
     * Test the process method with circular references in the data
     */
    public function testProcessWithCircularRefs()
    {
        $this->em->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($this->getRepositoryMock()));

        $this->validator
            ->expects($this->any())
            ->method('validate')
            ->will($this->returnValue($this->getConstraintViolationListMock()));

        $data = $this->getCategoryDataWithCircularRefs();
        $this->assertCount($data['result'], $this->processor->process($data['csv']));
    }

    /**
     * @return array
     */
    protected function getCategoryDataWithCircularRefs()
    {
        return array(
            'csv' => array(
                $this->getRow('root'),
                $this->getRow('child1', 'root'),
                $this->getRow('child2', 'child1'),
                $this->getRow('root2', 'child6'),
                $this->getRow('child4', 'root2'),
                $this->getRow('child5', 'root2'),
                $this->getRow('child6', 'root2'),
                $this->getRow('child7', 'child6'),
                $this->getRow('child8', 'child7'),
                $this->getRow('child9', 'child9'),
                $this->getRow('child10', 'child11'),
            ),
            'result' => 3
        );
    }

    /**
     * Create an array representing a single csv row
     * @param string $code
     * @param string $parent
     *
     * @return array
     */
    protected function getRow($code, $parent = null)
    {
        return array(
            'code'    => $code,
            'parent'  => $parent,
            'label-en_US'   => sprintf('%s (en)', ucfirst($code)),
            'label-fr_FR'   => sprintf('%s (fr)', ucfirst($code))
        );
    }

    /**
     * @param sring $code
     * @param sring $parent
     *
     * @return Category
     */
    protected function getCategory($code, $parent = null)
    {
        $category = new Category();
        $category->setCode($code);

        $english = new CategoryTranslation();
        $english->setLocale('en_US');
        $english->setLabel(ucfirst($code) . ' (en)');
        $english->setForeignKey($category);
        $category->addTranslation($english);

        $french = new CategoryTranslation();
        $french->setLocale('fr_FR');
        $french->setLabel(ucfirst($code) . ' (fr)');
        $french->setForeignKey($category);
        $category->addTranslation($french);

        if ($parent) {
            $category->setParent($this->getCategory($parent));
        }

        return $category;
    }
}
