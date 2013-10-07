<?php

namespace Oro\Bundle\ImportExportBundle\Tests\Unit\Serializer;

use Oro\Bundle\ImportExportBundle\Serializer\Serializer;

class SerializerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Serializer
     */
    protected $serializer;

    protected function setUp()
    {
        $this->serializer = new Serializer();
    }

    public function testConstruct()
    {
        $this->assertInstanceOf('Symfony\Component\Serializer\Serializer', $this->serializer);
    }
}
