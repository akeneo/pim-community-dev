<?php

namespace Oro\Bundle\FormBundle\Tests\Unit\Form\Twig;

use Oro\Bundle\FormBundle\Form\Twig\DataBlocks;

class DataBlocksTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Twig_Environment */
    private $twig;

    /** @var  DataBlocks */
    private $datablocks;

    public function setUp()
    {
        /** @var DataBlocks $dataBlocks */
        $this->datablocks = new DataBlocks();
    }

    public function testConstruct()
    {
        $this->assertInstanceOf(
            'Symfony\Component\PropertyAccess\PropertyAccessor',
            $this->readAttribute($this->datablocks, 'accessor')
        );
    }
}
