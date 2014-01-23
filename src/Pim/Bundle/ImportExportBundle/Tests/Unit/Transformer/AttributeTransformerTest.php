<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Transformer;

use Pim\Bundle\ImportExportBundle\Transformer\AttributeTransformer;

/**
 * Tests related class
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeTransformerTest extends EntityTransformerTestCase
{
    protected $attribute;
    protected $attributeManager;
    protected $entityCache;
    protected $transformer;
    protected $transformerRegistry;

    protected function setUp()
    {
        parent::setUp();
        $this->attribute = $this->getMock('Pim\Bundle\CatalogBundle\Entity\Attribute');
        $this->attributeManager = $this->getMock('Pim\Bundle\CatalogBundle\Manager\AttributeManagerInterface');
        $this->attributeManager->expects($this->any())
            ->method('getAttributeClass')
            ->will($this->returnValue('Pim\Bundle\CatalogBundle\Entity\Attribute'));
        $this->attributeManager->expects($this->any())
            ->method('getAttributeOptionClass')
            ->will($this->returnValue('Pim\Bundle\CatalogBundle\Entity\AttributeOption'));
        $this->entityCache = $this->getMockBuilder('Pim\Bundle\ImportExportBundle\Cache\EntityCache')
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeManager->expects($this->any())
            ->method('createAttribute')
            ->with($this->equalTo('type'))
            ->will($this->returnValue($this->attribute));
        $this->transformerRegistry = $this
            ->getMock('Pim\Bundle\ImportExportBundle\Transformer\EntityTransformerInterface');
        $this->transformer = new AttributeTransformer(
            $this->doctrine,
            $this->propertyAccessor,
            $this->guesser,
            $this->columnInfoTransformer,
            $this->transformerRegistry,
            $this->attributeManager,
            $this->entityCache
        );
        $this->addColumn('code');
        $this->transformerRegistry
            ->expects($this->any())
            ->method('transform')
            ->will(
                $this->returnCallback(
                    function ($class, $data) {
                        $this->assertEquals('Pim\Bundle\CatalogBundle\Entity\AttributeOption', $class);
                        $option = $this->getMock($class);
                        foreach ($data as $key => $value) {
                            $option->expects($this->any())
                                ->method('get' . ucfirst($key))
                                ->will($this->returnValue($value));
                        }

                        return $option;
                    }
                )
            );
    }
    protected function setupRepositories()
    {
        $this->repository = $this
            ->getMock('Pim\Bundle\CatalogBundle\Entity\Repository\ReferableEntityRepositoryInterface');
        $this->repository->expects($this->any())
            ->method('getReferenceProperties')
            ->will($this->returnValue(array('code')));

        $this->doctrine
            ->expects($this->any())
            ->method('getRepository')
            ->with($this->equalTo('Pim\Bundle\CatalogBundle\Entity\Attribute'))
            ->will($this->returnValue($this->repository));
    }

    public function getTransformData()
    {
        return array(
            'no_errors'     => array(false),
            'nested_errors' => array(true)
        );
    }

    /**
     * @dataProvider getTransformData
     */
    public function testTransform($nestedErrors)
    {
        $this->addColumn('type');
        $this->addColumn('col1');
        $this->addColumn('col2');
        $this->addColumn('options');
        $this->addColumn('attribute');

        if ($nestedErrors) {
            $errors = array(
                'co1' => array(
                    array('error')
                )
            );
        } else {
            $errors = array();
        }
        $this->transformerRegistry->expects($this->any())
            ->method('getErrors')
            ->with($this->equalTo('Pim\Bundle\CatalogBundle\Entity\AttributeOption'))
            ->will($this->returnValue($errors));

        $object = $this->transformer->transform(
            'Pim\Bundle\CatalogBundle\Entity\Attribute',
            array(
                'code' => 'code',
                'type' => 'type',
                'col1' => 'val1',
                'col2' => 'val2',
                'options' => array(
                    array(
                        'code' => 'o1code',
                        'col1' => 'o1val1',
                        'col2' => 'o1val2',
                    ),
                    array(
                        'col1' => 'o2val1',
                        'col2' => 'o2val2',
                    ),
                )
            )
        );

        $this->assertInstanceOf('Pim\Bundle\CatalogBundle\Entity\Attribute', $object);
        if ($nestedErrors) {
            $this->assertEquals(
                array(
                    'options' => array(
                        array('error'),
                        array('error')
                    )
                ),
                $this->transformer->getErrors('Pim\Bundle\CatalogBundle\Entity\Attribute')
            );
        } else {
            $this->assertEmpty($this->transformer->getErrors('Pim\Bundle\CatalogBundle\Entity\Attribute'));
        }
        $this->assertEquals('code_path-code', $object->code_path);
        $this->assertEquals('col1_path-val1', $object->col1_path);
        $this->assertEquals('col2_path-val2', $object->col2_path);
        $this->assertCount(6, $this->transformers);
    }
}
