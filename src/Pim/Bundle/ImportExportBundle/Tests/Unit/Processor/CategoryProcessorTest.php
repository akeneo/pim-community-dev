<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Processor;

use Pim\Bundle\ImportExportBundle\Processor\CategoryProcessor;
use Pim\Bundle\CatalogBundle\Entity\Category;

/**
 * Test related class
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryProcessorTest extends AbstractTransformerProcessorTestCase
{
    protected $processor;
    protected $transformer;
    protected $entityCache;

    protected function setUp()
    {
        parent::setUp();
        $this->entityCache = $this->getMockBuilder('Pim\Bundle\ImportExportBundle\Cache\EntityCache')
            ->disableOriginalConstructor()
            ->getMock();
        $this->transformer = $this->getMockBuilder('Pim\Bundle\ImportExportBundle\Transformer\ORMTransformer')
            ->disableOriginalConstructor()
            ->getMock();
        $this->processor = new CategoryProcessor(
            $this->validator,
            $this->translator,
            $this->transformer,
            $this->entityCache,
            'class'
        );

        $stepExecution = $this
            ->getMockBuilder('Oro\Bundle\BatchBundle\Entity\StepExecution')
            ->disableOriginalConstructor()
            ->getMock();
        $this->processor->setStepExecution($stepExecution);
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
                'type'    => 'switch',
                'options' => array(
                    'label' => 'pim_import_export.import.circularRefsChecked.label',
                    'help'  => 'pim_import_export.import.circularRefsChecked.help'
                )
            ),
        );
    }

    public function testGetConfigurationFields()
    {
        return $this->assertEquals(
            $this->getExpectedConfigurationFields(),
            $this->processor->getConfigurationFields()
        );
    }

    public function testTransform()
    {
        $data = array(
            'root'  => array('code' => 'root', 'key1' => 'value1', 'key2' => 'value2', 'parent' => null),
            'root2' => array('code' => 'root2', 'key1' => 'value3', 'key2' => 'value4'),
            'leaf'  => array('code' => 'leaf', 'key1' => 'value5', 'parent' => 'root')
        );
        $this->transformer->expects($this->any())
            ->method('getTransformedColumnsInfo')
            ->will($this->returnValue(array()));
        $this->transformer->expects($this->any())
            ->method('getErrors')
            ->will($this->returnValue(array()));
        $this->transformer->expects($this->any())
            ->method('transform')
            ->will(
                $this->returnCallback(
                    function ($class, $data) {
                        $this->assertEquals('class', $class);

                        return $this->getCategoryMock($data);
                    }
                )
            );
        $categories = $this->processor->process($data);

        $this->assertCategoriesData($data, $categories);
        $this->assertSame($categories['root'], $categories['leaf']->parent);
    }
    protected function assertCategoriesData($data, $categories)
    {
        foreach ($data as $index => $row) {
            unset($row['parent']);
            foreach ($row as $key => $value) {
                $this->assertEquals($value, $categories[$index]->$key);
            }
        }
    }

    protected function getCategoryMock($data)
    {
        $category = $this->getMockBuilder('Pim\Bundle\CatalogBundle\Model\CategoryInterface')
            ->setMethods(array('getParent', 'setParent', 'getCode'))
            ->getMock();
        $category->parent = null;
        foreach ($data as $key => $value) {
            $category->$key = $value;
        }
        $category->expects($this->any())
            ->method('getParent')
            ->will(
                $this->returnCallback(
                    function () use ($category) {
                        return $category->parent;
                    }
                )
            );
        $category->expects($this->any())
            ->method('getCode')
            ->will(
                $this->returnCallback(
                    function () use ($category) {
                        return $category->code;
                    }
                )
            );
        $category->expects($this->any())
            ->method('setParent')
            ->will(
                $this->returnCallback(
                    function ($parent) use ($category) {
                        $category->parent = $parent;
                    }
                )
            );

        return $category;
    }
}
