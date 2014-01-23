<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Transformer;

use Pim\Bundle\ImportExportBundle\Transformer\ORMAttributeTransformer;

/**
 * Tests related class
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OrmAttributeTransformerTest extends ORMTransformerTestCase
{
    protected $attribute;
    protected $attributeManager;
    protected $entityCache;
    protected $transformer;
    protected $optionRepository;

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
        $this->transformer = new ORMAttributeTransformer(
            $this->doctrine,
            $this->propertyAccessor,
            $this->guesser,
            $this->columnInfoTransformer,
            $this->attributeManager,
            $this->entityCache
        );
        $this->addColumn('code');
    }
    protected function setupRepositories()
    {
        $this->repository = $this
            ->getMock('Pim\Bundle\CatalogBundle\Entity\Repository\ReferableEntityRepositoryInterface');
        $this->repository->expects($this->any())
            ->method('getReferenceProperties')
            ->will($this->returnValue(['code']));

        $this->optionRepository = $this
            ->getMock('Pim\Bundle\CatalogBundle\Entity\Repository\ReferableEntityRepositoryInterface');
        $this->optionRepository->expects($this->any())
            ->method('getReferenceProperties')
            ->will($this->returnValue(['attribute', 'code']));

        $this->doctrine
            ->expects($this->any())
            ->method('getRepository')
            ->will(
                $this->returnValueMap(
                    [
                        ['Pim\Bundle\CatalogBundle\Entity\Attribute', $this->repository],
                        ['Pim\Bundle\CatalogBundle\Entity\AttributeOption', $this->optionRepository],
                    ]
                )
            );
    }

    public function testTransform()
    {
        $this->addColumn('type');
        $this->addColumn('col1');
        $this->addColumn('col2');
        $this->addColumn('options');
        $this->addColumn('attribute');

        $object = $this->transformer->transform(
            'Pim\Bundle\CatalogBundle\Entity\Attribute',
            [
                'code' => 'code',
                'type' => 'type',
                'col1' => 'val1',
                'col2' => 'val2',
                'options' => [
                    [
                        'code' => 'o1code',
                        'col1' => 'o1val1',
                        'col2' => 'o1val2',
                    ],
                    [
                        'col1' => 'o2val1',
                        'col2' => 'o2val2',
                    ],
                ]
            ]
        );
        $this->assertInstanceOf('Pim\Bundle\CatalogBundle\Entity\Attribute', $object);
        $this->assertEmpty($this->transformer->getErrors());
        $this->assertEquals('code_path-code', $object->code_path);
        $this->assertEquals('col1_path-val1', $object->col1_path);
        $this->assertEquals('col2_path-val2', $object->col2_path);
        $this->assertCount(6, $this->transformers);
    }
}
