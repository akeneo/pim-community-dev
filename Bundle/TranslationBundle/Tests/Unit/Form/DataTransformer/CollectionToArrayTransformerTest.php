<?php

namespace Oro\Bundle\TranslationBundle\Tests\Unit\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\TranslationBundle\Form\DataTransformer\CollectionToArrayTransformer;

class CollectionToArrayTransformerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param mixed $data
     * @param array $result
     *
     * @dataProvider transformDataProvider
     */
    public function testTransform($data, array $result)
    {
        $transformer = new CollectionToArrayTransformer();

        $this->assertEquals($result, $transformer->transform($data));
    }

    /**
     * @return array
     */
    public function transformDataProvider()
    {
        $testArray = array(1, 2, 3);

        return array(
            'empty array' => array(
                'data'   => array(),
                'result' => array(),
            ),
            'collection' => array(
                'data'   => new ArrayCollection($testArray),
                'result' => $testArray
            ),
        );
    }
}
