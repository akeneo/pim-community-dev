<?php

namespace Pim\Bundle\UIBundle\Form\Tests\Transformer;

use Pim\Bundle\UIBundle\Form\Transformer\AjaxEntityTransformer;

/**
 * Tests related class
 * 
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AjaxEntityTransformerTest extends \PHPUnit_Framework_TestCase
{
    protected $repository;
    
    protected function setUp()
    {
        $this->repository = $this->getMock('Pim\Bundle\UIBundle\Entity\Repository\OptionRepositoryInterface');
        $this->repository->expects($this->any())
            ->method('getOption')
            ->will(
                $this->returnCallback(
                    function ($id) {
                        return '_' . $id . '_';
                    }
                )
            );
        $this->repository->expects($this->any())
            ->method('getOptionId')
            ->will(
                $this->returnCallback(
                    function ($object) {
                        return $object->id;
                    }
                )
            );
        $this->repository->expects($this->any())
            ->method('getOptionLabel')
            ->will(
                $this->returnCallback(
                    function ($object, $locale) {
                        $this->assertEquals('locale', $locale);
                        return '_' . $object->id . '_';
                    }
                )
            );
    }
    
    protected function createTransformer($multiple)
    {
        $options = array(
            'collection_id' => 'collection_id',
            'multiple'      => $multiple,
            'locale'        => 'locale'
        );
        return new AjaxEntityTransformer($this->repository, $options);
    }
    
    protected function createObject($id)
    {
        $createObject = function($id) {
            if (null === $id) {
                return null;
            }

            $object = new \stdClass;
            $object->id = $id;        

            return $object;
        };
        
        return is_array($id) 
            ? array_map($createObject, $id)
            : $createObject($id);
    }

    public function getReverseTransformData()
    {
        return array(
            'single'         => array('1', '_1_', false),
            'single_empty'   => array('', null, false),
            'multiple'       => array('1,2,3', array('_1_', '_2_', '_3_'), true),
            'multiple_empty' => array('', array(), true),
        );
    }

    /**
     * @dataProvider getReverseTransformData
     */
    public function testReverseTransform($data, $expected, $multiple)
    {
        $transformer = $this->createTransformer($multiple);
        $this->assertEquals($expected, $transformer->reverseTransform($data));
    }
    
    public function getTransformData()
    {
        return array(
            'single'        => array(1, 1, false),
            'single_empty'  => array(null, null, false),
            'multiple'      => array(array(1, 2, 3), '1,2,3', true),
        );
    }

    /**
     * @dataProvider getTransformData
     */
    public function testTransform($data, $expected, $multiple)
    {
        $data = $this->createObject($data);
        $transformer = $this->createTransformer($multiple);
        $this->assertEquals($expected, $transformer->transform($data));
    }
    
    public function getGetOptionsData()
    {
        return array(
            'single'        => array(1, array('id' =>1, 'text' => '_1_'), false),
            'single_empty'  => array(null, null, false),
            'multiple'      => array(
                array(1, 2, 3), 
                array(
                    array('id' => 1, 'text' => '_1_'),
                    array('id' => 2, 'text' => '_2_'),
                    array('id' => 3, 'text' => '_3_')
                ), 
                true
            ),
        );
    }

    /**
     * @dataProvider getGetOptionsData
     */
    public function testGetOptions($data, $expected, $multiple)
    {
        $data = $this->createObject($data);
        $transformer = $this->createTransformer($multiple);
        $result = $transformer->getOptions($data);
        $this->assertEquals($expected, $result);
    }    
}
