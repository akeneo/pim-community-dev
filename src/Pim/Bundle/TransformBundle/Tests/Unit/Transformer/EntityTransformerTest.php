<?php

namespace Pim\Bundle\TransformBundle\Tests\Unit\Transformer;

use Pim\Bundle\TransformBundle\Transformer\EntityTransformer;

/**
 * Tests related class
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EntityTransformerTest extends EntityTransformerTestCase
{
    protected $transformer;

    protected function setUp()
    {
        parent::setUp();
        $this->transformer = new EntityTransformer(
            $this->doctrine,
            $this->propertyAccessor,
            $this->guesser,
            $this->columnInfoTransformer
        );
        $this->addColumn('code');
    }

    public function getTransformData()
    {
        return array(
            'referable'     => array(true),
            'non_referable' => array(false)
        );
    }

    /**
     * @dataProvider getTransformData
     */
    public function testTransform($referableRepository)
    {
        $this->setupRepositories($referableRepository);
        $this->addColumn('col1');
        $this->addColumn('col2', true, false, true);
        $this->addColumn('skipped', true, true);

        $object = $this->transformer->transform(
            'stdClass',
            array('code'  => 'code', 'col1' => 'val1', 'col2' => 'val2', 'skipped' => 'skipped'),
            array('prop3' => 'val3', 'prop4' => 'val4')
        );
        $this->assertInstanceOf('stdClass', $object);
        $this->assertEmpty($this->transformer->getErrors('stdClass'));
        $this->assertEquals('val3', $object->prop3);
        $this->assertEquals('val4', $object->prop4);
        $this->assertEquals('code_path-code', $object->code_path);
        $this->assertEquals('col1_path-val1', $object->col1_path);
        $this->assertEquals('col2_path-val2_entityupdater', $object->col2_path);
        $this->assertObjectNotHasAttribute('skipped', $object);
        $this->assertCount(4, $this->transformers);
    }

    public function testFailingTransformer()
    {
        $this->setupRepositories();
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
            $this->transformer->getErrors('stdClass')
        );
        $this->assertCount(2, $this->transformer->getTransformedColumnsInfo('stdClass'));
    }

    /**
     * @expectedException Pim\Bundle\TransformBundle\Exception\UnknownColumnException
     * @expectedExceptionMessage Properties [col1] do not exist in stdClass.
     */
    public function testMissingTransformer()
    {
        $this->setupRepositories();
        $this->addColumn('col1', false);
        $this->addColumn('col2');

        $this->transformer->transform(
            'stdClass',
            array('code' => 'code', 'col1' => 'val1', 'col2' => 'val2')
        );
    }

    /**
     * @expectedException \Pim\Bundle\TransformBundle\Exception\MissingIdentifierException
     */
    public function testMissingIdentifier()
    {
        $this->setupRepositories();
        $this->addColumn('col1');

        $this->transformer->transform(
            'stdClass',
            array('col1' => 'val1')
        );
    }
}
