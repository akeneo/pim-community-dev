<?php

namespace Akeneo\Platform\Bundle\UIBundle\Tests\Unit\Form\Transformer;

use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\AttributeOptionRepository;
use Akeneo\Platform\Bundle\UIBundle\Form\Transformer\AjaxEntityTransformer;
use PHPUnit\Framework\TestCase;

/**
 * Tests related class
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AjaxEntityTransformerTest extends TestCase
{
    protected $repository;

    /**
     * @{@inheritdoc}
     */
    protected function setUp()
    {
        $this->repository = $this->createMock(
            AttributeOptionRepository::class
        );

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
        $options = [
            'collection_id' => 'collection_id',
            'multiple'      => $multiple,
            'locale'        => 'locale'
        ];

        return new AjaxEntityTransformer($this->repository, $options);
    }

    protected function createObject($id)
    {
        $createObject = function ($id) {
            if (null === $id) {
                return null;
            }

            $object = new \stdClass();
            $object->id = $id;

            return $object;
        };

        return is_array($id)
            ? array_map($createObject, $id)
            : $createObject($id);
    }

    public function getReverseTransformData()
    {
        return [
            'single'         => ['1', '_1_', false],
            'single_empty'   => ['', null, false],
            'multiple'       => ['1,2,3', ['_1_', '_2_', '_3_'], true],
            'multiple_empty' => ['', [], true],
        ];
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
        return [
            'single'        => [1, 1, false],
            'single_empty'  => [null, null, false],
            'multiple'      => [[1, 2, 3], '1,2,3', true],
        ];
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
        return [
            'single'        => [1, ['id' => 1, 'text' => '_1_'], false],
            'single_empty'  => [null, null, false],
            'multiple'      => [
                [1, 2, 3],
                [
                    ['id' => 1, 'text' => '_1_'],
                    ['id' => 2, 'text' => '_2_'],
                    ['id' => 3, 'text' => '_3_']
                ],
                true
            ],
        ];
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
