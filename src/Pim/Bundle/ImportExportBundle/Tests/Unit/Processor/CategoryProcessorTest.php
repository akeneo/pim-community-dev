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
class CategoryProcessorTest extends TransformerProcessorTestCase
{
    protected $processor;
    protected $transformer;
    protected $entityCache;
    protected $stepExecution;

    protected function setUp()
    {
        parent::setUp();
        $this->entityCache = $this->getMockBuilder('Pim\Bundle\ImportExportBundle\Cache\EntityCache')
            ->disableOriginalConstructor()
            ->getMock();
        $this->transformer->expects($this->any())
            ->method('getTransformedColumnsInfo')
            ->will($this->returnValue(array('columns_info')));
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
        $this->processor = new CategoryProcessor(
            $this->validator,
            $this->translator,
            $this->transformer,
            $this->entityCache,
            'class'
        );

        $this->stepExecution = $this
            ->getMockBuilder('Oro\Bundle\BatchBundle\Entity\StepExecution')
            ->disableOriginalConstructor()
            ->getMock();

        $this->validator->expects($this->any())
            ->method('validate')
            ->will($this->returnArgument(3));
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
        $this->processor->setStepExecution($this->stepExecution);
        $data = array(
            'root'    => array('code' => 'root', 'key1' => 'value1', 'key2' => 'value2', 'parent' => null),
            'root2'   => array('code' => 'root2', 'key1' => 'value3', 'key2' => 'value4'),
            'leaf'    => array('code' => 'leaf', 'key1' => 'value5', 'parent' => 'root'),
            'subleaf' => array('code' => 'subleaf', 'parent' => 'leaf'),
            'leaf2'    => array('code' => 'leaf2', 'parent' => 'root'),
        );

        $this->transformer->expects($this->any())
            ->method('getErrors')
            ->will($this->returnValue(array()));

        $categories = $this->processor->process($data);

        $this->assertCategoriesData($data, $categories);
        $this->assertSame($categories['root'], $categories['leaf']->parent);
        $this->assertSame($categories['root'], $categories['leaf2']->parent);
        $this->assertSame($categories['leaf'], $categories['subleaf']->parent);
    }

    public function testTransformWithPersistedParent()
    {
        $this->processor->setStepExecution($this->stepExecution);
        $data = array(
            'root'    => array('code' => 'root', 'key1' => 'value1', 'key2' => 'value2', 'parent' => null),
            'root2'   => array('code' => 'root2', 'key1' => 'value3', 'key2' => 'value4'),
            'leaf'    => array('code' => 'leaf', 'key1' => 'value5', 'parent' => 'persisted'),
            'subleaf' => array('code' => 'subleaf', 'parent' => 'leaf'),
            'leaf2'    => array('code' => 'leaf2', 'parent' => 'root'),
        );

        $persisted = $this->getCategoryMock(array('code' => 'persisted', 'parent' => null));
        $this->entityCache->expects($this->once())
            ->method('find')
            ->with($this->equalTo('class'), $this->equalTo('persisted'))
            ->will($this->returnValue($persisted));

        $this->transformer->expects($this->any())
            ->method('getErrors')
            ->will($this->returnValue(array()));

        $categories = $this->processor->process($data);

        $this->assertCategoriesData($data, $categories);
        $this->assertSame($persisted, $categories['leaf']->parent);
        $this->assertSame($categories['root'], $categories['leaf2']->parent);
        $this->assertSame($categories['leaf'], $categories['subleaf']->parent);
    }

    public function testTransformWithMissingParent()
    {
        $this->processor->setStepExecution($this->stepExecution);
        $data = array(
            'root'    => array('code' => 'root', 'key1' => 'value1', 'key2' => 'value2', 'parent' => null),
            'root2'   => array('code' => 'root2', 'key1' => 'value3', 'key2' => 'value4'),
            'leaf'    => array('code' => 'leaf', 'key1' => 'value5', 'parent' => 'bad_root'),
            'subleaf' => array('code' => 'subleaf', 'parent' => 'leaf'),
            'leaf2'    => array('code' => 'leaf2', 'parent' => 'root'),
        );

        $this->transformer->expects($this->any())
            ->method('getErrors')
            ->will($this->returnValue(array()));

        $this->assertErrors(
            array(
                'leaf'    => 'parent: <tr>No category with code bad_root</tr>',
                'subleaf' => 'parent: <tr>No category with code leaf</tr>',
            ),
            $data
        );

        $categories = $this->processor->process($data);

        $validData = $data;
        unset($validData['leaf'], $validData['subleaf']);
        $this->assertCategoriesData($validData, $categories);
        $this->assertSame($categories['root'], $categories['leaf2']->parent);

    }

    public function testTransformWithCircularReferences()
    {
        $this->processor->setStepExecution($this->stepExecution);
        $data = array(
            'root'    => array('code' => 'root', 'key1' => 'value1', 'key2' => 'value2', 'parent' => 'leaf'),
            'root2'   => array('code' => 'root2', 'key1' => 'value3', 'key2' => 'value4'),
            'leaf'    => array('code' => 'leaf', 'key1' => 'value5', 'parent' => 'root'),
            'subleaf' => array('code' => 'subleaf', 'parent' => 'leaf'),
            'leaf2'    => array('code' => 'leaf2', 'parent' => 'root2'),
        );

        $this->transformer->expects($this->any())
            ->method('getErrors')
            ->will($this->returnValue(array()));

        $this->assertErrors(
            array(
                'subleaf' => 'parent: <tr>Circular reference</tr>',
                'leaf'    => 'parent: <tr>Circular reference</tr>',
                'root'    => 'parent: <tr>Circular reference</tr>',
            ),
            $data
        );

        $categories = $this->processor->process($data);

        $validData = $data;
        unset($validData['leaf'], $validData['subleaf'], $validData['root']);
        $this->assertCategoriesData($validData, $categories);
    }

    public function testTransformWithErrors()
    {
        $this->processor->setStepExecution($this->stepExecution);
        $data = array(
            'root'    => array('code' => 'root', 'key1' => 'value1', 'key2' => 'value2', 'parent' => null),
            'root2'   => array('code' => 'root2', 'key1' => 'value3', 'key2' => 'value4'),
            'leaf'    => array('code' => 'leaf', 'key1' => 'value5', 'parent' => 'root'),
            'subleaf' => array('code' => 'subleaf', 'parent' => 'leaf'),
            'leaf2'    => array('code' => 'leaf2', 'parent' => 'root2'),
        );

        $iteration = 0;
        $this->transformer->expects($this->any())
            ->method('getErrors')
            ->will(
                $this->returnCallback(
                    function () use (&$iteration) {
                        $iteration++;

                        return ($iteration==2)
                            ? array('key1' => array(array('Error')))
                            : array();
                    }
                )
            );

        $this->assertErrors(
            array(
                'root2'   => 'key1: <tr>Error</tr>',
                'leaf2'   => 'parent: <tr>No category with code root2</tr>',
            ),
            $data
        );

        $categories = $this->processor->process($data);

        $validData = $data;
        unset($validData['root2'], $validData['leaf2']);
        $this->assertCategoriesData($validData, $categories);
        $this->assertSame($categories['root'], $categories['leaf']->parent);
        $this->assertSame($categories['leaf'], $categories['subleaf']->parent);
    }

    /**
     * @expectedException Oro\Bundle\BatchBundle\Item\InvalidItemException
     * @expectedExceptionMessage key1: <tr>Error</tr>
     */
    public function testTransformWithErrorsWithoutStepExecution()
    {
        $data = array(
            'root'    => array('code' => 'root', 'key1' => 'value1', 'key2' => 'value2', 'parent' => null),
            'root2'   => array('code' => 'root2', 'key1' => 'value3', 'key2' => 'value4'),
            'leaf'    => array('code' => 'leaf', 'key1' => 'value5', 'parent' => 'root'),
            'subleaf' => array('code' => 'subleaf', 'parent' => 'leaf'),
            'leaf2'    => array('code' => 'leaf2', 'parent' => 'root2'),
        );

        $iteration = 0;
        $this->transformer->expects($this->any())
            ->method('getErrors')
            ->will(
                $this->returnCallback(
                    function () use (&$iteration) {
                        $iteration++;

                        return ($iteration==2)
                            ? array('key1' => array(array('Error')))
                            : array();
                    }
                )
            );

        $categories = $this->processor->process($data);
    }

    protected function assertErrors($errorMessages, $data)
    {
        $this->stepExecution->expects($this->exactly(count($errorMessages)))
            ->method('incrementSummaryInfo')
            ->with($this->equalTo('skip'));

        $this->stepExecution->expects($this->exactly(count($errorMessages)))
            ->method('addWarning')
            ->will(
                $this->returnCallback(
                    function ($name, $message, $item) use (&$errorMessages, $data) {
                        list($code, $expectedMessage) = each($errorMessages);
                        $this->assertEquals('category_processor', $name);
                        $this->assertEquals($expectedMessage, $message);
                        $expectedData = $data[$code];
                        unset($expectedData['parent']);
                        $this->assertEquals($expectedData, $item);
                    }
                )
            );

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
