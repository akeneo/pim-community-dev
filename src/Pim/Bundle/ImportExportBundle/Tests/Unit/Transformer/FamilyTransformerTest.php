<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Transformer;

use Pim\Bundle\ImportExportBundle\Transformer\FamilyTransformer;

/**
 * Tests related class
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyTransformerTest extends EntityTransformerTestCase
{
    protected $transformerRegistry;
    protected $familyFactory;
    protected $transformer;
    protected $family;

    protected function setUp()
    {
        parent::setUp();
        $this->familyFactory = $this->getMockBuilder('Pim\Bundle\CatalogBundle\Factory\FamilyFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->transformerRegistry = $this
            ->getMock('Pim\Bundle\ImportExportBundle\Transformer\EntityTransformerInterface');
        $this->transformerRegistry
            ->expects($this->any())
            ->method('transform')
            ->will(
                $this->returnCallback(
                    function ($class, $data) {
                        $this->assertEquals('Pim\Bundle\CatalogBundle\Entity\AttributeRequirement', $class);
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

        $this->transformer = new FamilyTransformer(
            $this->doctrine,
            $this->propertyAccessor,
            $this->guesser,
            $this->columnInfoTransformer,
            $this->transformerRegistry,
            $this->familyFactory,
            'Pim\Bundle\CatalogBundle\Entity\AttributeRequirement'
        );
        $this->addColumn('code');
        $this->setupRepositories();

        $this->family = $this->getMock('Pim\Bundle\CatalogBundle\Entity\Family');
        $this->familyFactory
            ->expects($this->any())
            ->method('createFamily')
            ->will($this->returnValue($this->family));
    }

    protected function setupRepositories()
    {
        $this->repository = $this->getMockBuilder('Pim\Bundle\CatalogBundle\Doctrine\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $this->doctrine
            ->expects($this->any())
            ->method('getRepository')
            ->with($this->equalTo('Pim\Bundle\CatalogBundle\Entity\AttributeRequirement'))
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
            ->with($this->equalTo('Pim\Bundle\CatalogBundle\Entity\AttributeRequirement'))
            ->will($this->returnValue($errors));

        $object = $this->transformer->transform(
            'Pim\Bundle\CatalogBundle\Entity\AttributeRequirement',
            array(
                'code' => 'code',
                'type' => 'type',
                'col1' => 'val1',
                'col2' => 'val2',
                'requirements' => array(
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

        $this->assertInstanceOf('Pim\Bundle\CatalogBundle\Entity\Family', $object);
        if ($nestedErrors) {
            $this->assertEquals(
                array(
                    'requirements' => array(
                        array('error'),
                        array('error'),
                        array('error'),
                        array('error'),
                        array('error'),
                    )
                ),
                $this->transformer->getErrors('Pim\Bundle\CatalogBundle\Entity\AttributeRequirement')
            );
        } else {
            $this->assertEmpty($this->transformer->getErrors('Pim\Bundle\CatalogBundle\Entity\AttributeRequirement'));
        }
        $this->assertEquals('code_path-code', $object->code_path);
        $this->assertEquals('col1_path-val1', $object->col1_path);
        $this->assertEquals('col2_path-val2', $object->col2_path);
        $this->assertCount(6, $this->transformers);
    }
}
