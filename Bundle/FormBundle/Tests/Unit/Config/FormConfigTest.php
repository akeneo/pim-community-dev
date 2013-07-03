<?php

namespace Oro\Bundle\FormBundle\Tests\Unit\Config;

use Oro\Bundle\FormBundle\Config\BlockConfig;
use Oro\Bundle\FormBundle\Config\FormConfig;
use Oro\Bundle\FormBundle\Config\SubBlockConfig;

class FormConfigTest extends \PHPUnit_Framework_TestCase
{
    /** @var  FormConfig  */
    private $formConfig;

    private $blocks = array();

    private $testSubBlocksConfig = array(
        'common' => array(
            'title'    => 'Common Setting',
            'priority' => 3,
        ),
        'custom' => array(
            'title'    => 'Custom Setting',
            'priority' => 2,
        ),
        'last' => array(
            'title'    => 'Last SubBlock',
            'priority' => 1,
        )
    );

    public function setUp()
    {
        $this->formConfig = new FormConfig();
    }

    public function testAddBlock()
    {
        /** test getBlocks without any adjusted block(s) */
        $this->assertEquals(array(), $this->formConfig->getBlocks());

        /** test hasBlock without any adjusted block(s) */
        $this->assertFalse($this->formConfig->hasBlock('testBlock'));

        /** @var BlockConfig $blockConfig */
        $blockConfig = new BlockConfig('testBlock');
        $blockConfig
            ->setTitle('Test Block')
            ->setClass('Oro\Bundle\UserBundle\Entity\User')
            ->setPriority(1);

        $subblocks      = array();
        $subblocksArray = array();
        foreach($this->testSubBlocksConfig as $code => $data) {
            $subBlock = new SubBlockConfig($code);
            $subBlock
                ->setTitle($data['title'])
                ->setPriority($data['priority']);
            $blockConfig->addSubBlock($subBlock);

            $subblocks[] = $subBlock;
            $subblocksArray[$code] = $subBlock->toArray();
        }

        $this->formConfig->addBlock($blockConfig);
        $this->blocks[] = $blockConfig;

        /** test hasBlock */
        $this->assertTrue($this->formConfig->hasBlock('testBlock'));

        /** test getBlock */
        $this->assertEquals($blockConfig, $this->formConfig->getBlock('testBlock'));

        /** test getSubBlock */
        $this->assertEquals($subblocks[0], $this->formConfig->getSubBlocks('testBlock', 'common'));

        /** test toArray() */
        $this->assertEquals(array('testBlock' => array(
                'title'     => 'Test Block',
                'class'     => 'Oro\Bundle\UserBundle\Entity\User',
                'subblocks' => $subblocksArray
            )),
            $this->formConfig->toArray()
        );

        /** test getBlocks */
        $this->formConfig->setBlocks($this->blocks);
        $this->assertEquals(
            $this->blocks,
            $this->formConfig->getBlocks()
        );
    }

    public function testException()
    {
        /** test getSubBlock Exception */
        $this->setExpectedException(
            '\PHPUnit_Framework_Error_Notice', 'Undefined index: testBlock'
        );
        $this->formConfig->getBlock('testBlock');
    }
}
