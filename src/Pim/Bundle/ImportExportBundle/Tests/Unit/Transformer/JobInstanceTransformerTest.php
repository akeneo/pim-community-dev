<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Transformer;

use Pim\Bundle\ImportExportBundle\Transformer\JobInstanceTransformer;

/**
 * Tests related class
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobInstanceTransformerTest extends EntityTransformerTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->transformer = new JobInstanceTransformer(
            $this->doctrine,
            $this->propertyAccessor,
            $this->guesser,
            $this->columnInfoTransformer
        );
        $this->addColumn('code');
        $this->repository = $this->getMockBuilder('Pim\Bundle\CatalogBundle\Doctrine\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $this->doctrine
            ->expects($this->any())
            ->method('getRepository')
            ->with($this->equalTo('stdClass'))
            ->will($this->returnValue($this->repository));
    }

    public function testTransform()
    {
        $this->addColumn('col1');

        $object = $this->transformer->transform(
            'stdClass',
            array('code' => 'code', 'col1' => 'val1')
        );
        $this->assertInstanceOf('stdClass', $object);
        $this->assertEmpty($this->transformer->getErrors('stdClass'));
        $this->assertEquals('code_path-code', $object->code_path);
        $this->assertEquals('col1_path-val1', $object->col1_path);
        $this->assertCount(2, $this->transformers);
    }
}
