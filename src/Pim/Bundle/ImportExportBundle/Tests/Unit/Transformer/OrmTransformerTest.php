<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Transformer;

use Pim\Bundle\ImportExportBundle\Transformer\OrmTransformer;

/**
 * Tests related class
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OrmTransformerTest extends OrmTransformerTestCase
{
    protected $transformer;

    protected function setUp()
    {
        parent::setUp();
        $this->transformer = new OrmTransformer(
            $this->doctrine,
            $this->propertyAccessor,
            $this->guesser,
            $this->labelTransformer
        );
        $this->addColumn('code');
    }

    public function testTransform()
    {
        $this->addColumn('col1');
        $this->addColumn('col2');

        $object = $this->transformer->transform(
            'stdClass',
            array('code' => 'code', 'col1' => 'val1', 'col2' => 'val2'),
            array('prop3' => 'val3', 'prop4' => 'val4')
        );
        $this->assertInstanceOf('stdClass', $object);
        $this->assertEmpty($this->transformer->getErrors());
        $this->assertEquals('val3', $object->prop3);
        $this->assertEquals('val4', $object->prop4);
        $this->assertEquals('code_path-code', $object->code_path);
        $this->assertEquals('col1_path-val1', $object->col1_path);
        $this->assertEquals('col2_path-val2', $object->col2_path);
        $this->assertCount(3, $this->transformers);
    }

    public function testFailingTransformer()
    {
        $this->addColumn('col1', false);
        $this->addColumn('col2');
        $this->addTransformer('col1_path', true);

        $object = $this->transformer->transform(
            'stdClass',
            array('code' => 'code', 'col1' => 'val1', 'col2' => 'val2')
        );
        $this->assertEquals('code_path-code', $object->code_path);
        $this->assertEquals('col2_path-val2', $object->col2_path);
        $this->assertEquals(
            array('col1' => array(array('error_message', array('error_parameters')))),
            $this->transformer->getErrors()
        );
        $this->assertCount(2, $this->transformer->getTransformedColumnsInfo());
    }

    /**
     * @expectedException Pim\Bundle\ImportExportBundle\Exception\UnknownColumnException
     * @expectedExceptionMessage Columns col1 do not exist.
     */
    public function testMissingTransformer()
    {
        $this->addColumn('col1', false);
        $this->addColumn('col2');

        $this->transformer->transform(
            'stdClass',
            array('code' => 'code', 'col1' => 'val1', 'col2' => 'val2')
        );
    }
}
