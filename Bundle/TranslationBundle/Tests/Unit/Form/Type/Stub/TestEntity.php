<?php

namespace Oro\Bundle\TranslationBundle\Tests\Unit\Form\Type\Stub;

class TestEntity
{
    /**
     * @var int
     */
    public $testId;

    /**
     * @var string
     */
    public $testProperty;

    /**
     * @param int    $id
     * @param string $property
     */
    public function __construct($id, $property)
    {
        $this->testId = $id;
        $this->testProperty = $property;
    }
}
