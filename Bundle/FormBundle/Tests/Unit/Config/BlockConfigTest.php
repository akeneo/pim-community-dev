<?php

namespace Oro\Bundle\FormBundle\Tests\Unit\Config;

use Oro\Bundle\FormBundle\Config\BlockConfig;
use Oro\Bundle\FormBundle\Config\SubBlockConfig;

class BlockConfigTest extends \PHPUnit_Framework_TestCase
{
    /** @var  BlockConfig */
    private $blockConfig;

    /** @var  SubBlockConfig */
    private $subBlock;

    /** @var string form DataBlock code */
    private $blockCode = 'datagrid';

    private $testCode = 'testCode';
    private $testTitle = 'testTitle';
    private $testDescription = 'testDescription';

    private $testClass = 'Oro\Bundle\UserBundle\Entity\User';

    private $testBlockConfig = array(
        'block_config' => array(
            'type' => array(
                'title'     => 'Doctrine Type',
                'priority'  => 1,
                'subblocks' => array(
                    'common' => array(
                        'title'    => 'Common Setting',
                        'priority' => 1,
                        'useSpan'  => true
                    ),
                    'custom' => array(
                        'title'    => 'Custom Setting',
                        'priority' => 2,
                        'useSpan'  => true
                    ),
                )
            ),
        )
    );

    private $testSubBlocks = array();

    private $testSubBlocksConfig = array(
        'common' => array(
            'title'       => 'Common Setting',
            'priority'    => 3,
            'description' => 'some description',
            'useSpan'     => true
        ),
        'custom' => array(
            'title'    => 'Custom Setting',
            'priority' => 2,
            'useSpan'  => true
        ),
        'last'   => array(
            'title'    => 'Last SubBlock',
            'priority' => 1,
            'useSpan'  => true
        )
    );

    public function setUp()
    {
        $this->blockConfig = new BlockConfig($this->blockCode);
    }

    public function testProperties()
    {
        /** test getCode */
        $this->assertEquals($this->blockCode, $this->blockConfig->getCode());

        /** test setCode */
        $this->blockConfig->setCode($this->testCode);
        $this->assertEquals($this->testCode, $this->blockConfig->getCode());

        /** test getTitle */
        $this->assertNull($this->blockConfig->getTitle());

        /** test setTitle */
        $this->blockConfig->setTitle($this->testTitle);
        $this->assertEquals($this->testTitle, $this->blockConfig->getTitle());

        /** test getPriority */
        $this->assertNull($this->blockConfig->getPriority());

        /** test setPriority */
        $this->blockConfig->setPriority(10);
        $this->assertEquals(10, $this->blockConfig->getPriority());

        /** test getClass */
        $this->assertNull($this->blockConfig->getClass());

        /** test setClass */
        $this->blockConfig->setClass($this->testClass);
        $this->assertEquals($this->testClass, $this->blockConfig->getClass());

        /** test getSubBlock */
        $this->assertEquals(array(), $this->blockConfig->getSubBlocks());

        /** test setSubBlocks */
        $this->blockConfig->setSubBlocks($this->testSubBlocks);
        $this->assertEquals($this->testSubBlocks, $this->blockConfig->getSubBlocks());

        /** test setDescription */
        $this->blockConfig->setDescription($this->testDescription);
        $this->assertEquals($this->testDescription, $this->blockConfig->getDescription());

        /** test hasSubBlock */
        $this->assertFalse($this->blockConfig->hasSubBlock('testSubBlock'));

        /** test setSubBlock */
        $subblocks = array();
        foreach ($this->testSubBlocksConfig as $code => $data) {
            $blockDescription = !empty($data['description']) ? $data['description'] : null;
            $subblocks[]      = array(
                'code'        => $code,
                'title'       => $data['title'],
                'data'        => array('some_data'),
                'description' => $blockDescription,
                'useSpan'     => true
            );
            $subBlock         = new SubBlockConfig($code);

            /** test SubBlockConfig set/get Title/Priority/Code */
            $subBlock->setTitle($data['title']);
            $this->assertEquals($data['title'], $subBlock->getTitle());

            $subBlock->setPriority($data['priority']);
            $this->assertEquals($data['priority'], $subBlock->getPriority());

            $subBlock->setCode($code);
            $this->assertEquals($code, $subBlock->getCode());

            $subBlock->setData(array('some_data'));
            $this->assertEquals(array('some_data'), $subBlock->getData());

            $subBlock->setUseSpan(true);
            $this->assertTrue($subBlock->getUseSpan());

            $subBlock->setDescription($blockDescription);
            $this->assertEquals($blockDescription, $subBlock->getDescription());

            /** test SubBlockConfig addSubBlock */
            $this->blockConfig->addSubBlock($subBlock);
            $this->assertEquals($subBlock, $this->blockConfig->getSubBlock($code));

            $this->testSubBlocks[] = $subBlock;
        }

        $this->blockConfig->setSubBlocks($this->testSubBlocks);
        $this->assertEquals($this->testSubBlocks, $this->blockConfig->getSubBlocks());

        $this->assertEquals(
            array(
                'title'       => $this->testTitle,
                'class'       => $this->testClass,
                'subblocks'   => $subblocks,
                'description' => $this->testDescription,
            ),
            $this->blockConfig->toArray()
        );
    }

    public function testException()
    {
        /** test getSubBlock Exception */
        $this->setExpectedException(
            '\PHPUnit_Framework_Error_Notice',
            'Undefined index: testSubBlock'
        );
        $this->blockConfig->getSubBlock('testSubBlock');
    }

    public function testBlockConfig()
    {
        $this->assertNull($this->blockConfig->getBlockConfig());

        $this->blockConfig->setBlockConfig($this->testBlockConfig);
        $this->assertEquals(
            $this->testBlockConfig,
            $this->readAttribute($this->blockConfig, 'blockConfig')
        );
    }
}
