<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Processor;

use Pim\Bundle\ImportExportBundle\Processor\SerializerProcessor;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Serializer;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SerializerProcessorTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $serializer = new Serializer(
            array(new GetSetMethodNormalizer),
            array(new XmlEncoder)
        );
        $this->processor = new SerializerProcessor($serializer);
    }

    public function testInstanceOfItemProcessorInterface()
    {
        $this->assertInstanceOf('Pim\Bundle\BatchBundle\Item\ItemProcessorInterface', $this->processor);
    }

    public function testProcess()
    {
        $this->processor->setFormat('xml');

        $this->assertEquals(
            "<?xml version=\"1.0\"?>\n<response><item key=\"0\">foo</item></response>\n",
            $this->processor->process(array('foo'))
        );
    }

    private function getSerializerMock()
    {
        return $this->getMock('Symfony\Component\Serializer\Serializer');
    }
}

