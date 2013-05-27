<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Datagrid\Stub;

/**
 * Stub entity class
 */
class StubEntity
{
    private $privateProperty;

    public $publicProperty;

    private $booleanProperty;

    public function __construct($privateProperty = null, $publicProperty = null, $booleanProperty = false)
    {
        $this->privateProperty = $privateProperty;
        $this->publicProperty = $publicProperty;
        $this->booleanProperty = (boolean)$booleanProperty;
    }

    public function getPrivateProperty()
    {
        return $this->privateProperty;
    }

    public function isBooleanProperty()
    {
        return $this->booleanProperty;
    }
}
