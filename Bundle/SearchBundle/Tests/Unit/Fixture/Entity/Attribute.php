<?php
namespace Oro\Bundle\SearchBundle\Tests\Unit\Fixture\Entity;

class Attribute
{
    /**
     * @var string $code
     */
    protected $code;

    /**
     * @var string $backendType
     */
    protected $backendType;

    protected $searchable;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->searchable   = true;
        $this->code = 'test_attribute';
    }

    public function isSearchable()
    {
        return $this->searchable;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getBackendType()
    {
        return $this->backendType;
    }

    public function setBackendType($backendType)
    {
        $this->backendType = $backendType;

        return $this;
    }

    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }
}
