<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Permission;

use Oro\Bundle\SecurityBundle\Acl\Permission\MaskBuilder;

class MaskBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider constructorWithNonIntegerProvider
     */
    public function testConstructorWithNonInteger($invalidMask)
    {
        new MaskBuilder($invalidMask);
    }

    public function testConstructorWithoutArguments()
    {
        $builder = new MaskBuilder();

        $this->assertEquals(0, $builder->get());
    }

    public function testConstructor()
    {
        $builder = new MaskBuilder(123456);

        $this->assertEquals(123456, $builder->get());
    }

    /**
     * @dataProvider addAndRemoveProvider
     */
    public function testMaskConstant($maskName, $mask)
    {
        $count = 0;
        $bitmask = decbin($mask);
        for ($i = 0; $i < strlen($bitmask); $i++) {
            if ('1' === $bitmask[$i]) {
                $count++;
            }
        }

        $this->assertEquals(1, $count, sprintf('Each mask must set one and only one bit. Bitmask: %s', $bitmask));
    }

    /**
     * @dataProvider addAndRemoveProvider
     */
    public function testAddAndRemove($maskName, $mask)
    {
        $builder = new MaskBuilder();
        $builder->add($maskName);
        $this->assertEquals($mask, $builder->get());

        $builder->remove($maskName);
        $this->assertEquals(0, $mask = $builder->get());
    }

    /**
     * @dataProvider addAndRemoveProvider
     */
    public function testAddAndRemoveCaseInsensitive($maskName, $mask)
    {
        $builder = new MaskBuilder();
        $builder->add(strtolower($maskName));
        $this->assertEquals($mask, $builder->get());

        $builder->remove(strtolower($maskName));
        $this->assertEquals(0, $mask = $builder->get());
    }

    public function testGetPattern()
    {
        $builder = new MaskBuilder();
        $this->assertEquals(MaskBuilder::ALL_OFF, $builder->getPattern());
        $this->assertEquals(MaskBuilder::ALL_OFF_BRIEF, $builder->getPattern(true));

        $builder->add('view_basic');
        $expected = substr(MaskBuilder::ALL_OFF, 0, strlen(MaskBuilder::ALL_OFF) - 1) . 'V';
        $this->assertEquals($expected, $builder->getPattern());
        $expectedBrief = substr(MaskBuilder::ALL_OFF_BRIEF, 0, strlen(MaskBuilder::ALL_OFF_BRIEF) - 1) . 'V';
        $this->assertEquals($expectedBrief, $builder->getPattern(true));

        $builder->add('view_local');
        $expected = str_replace('. basic:', 'V basic:', $expected);
        $this->assertEquals($expected, $builder->getPattern());

        $builder->add('view_deep');
        $expected = str_replace('. local:', 'V local:', $expected);
        $this->assertEquals($expected, $builder->getPattern());

        $builder->add('view_global');
        $expected = str_replace('. deep:', 'V deep:', $expected);
        $this->assertEquals($expected, $builder->getPattern());
    }

    public function testGetPatternWithUndefinedMask()
    {
        $builder = new MaskBuilder((integer)2147483648);
        $delim = strpos(MaskBuilder::ALL_OFF, ' ');
        $expected =
            substr(MaskBuilder::ALL_OFF, 0, $delim + 1)
            . '*'
            . substr(MaskBuilder::ALL_OFF, $delim + 2, strlen(MaskBuilder::ALL_OFF) - $delim - 2);
        $this->assertEquals($expected, $builder->getPattern());
    }

    public function testReset()
    {
        $builder = new MaskBuilder();
        $this->assertEquals(0, $builder->get());

        $builder->add('view_basic');
        $this->assertTrue($builder->get() > 0);

        $builder->reset();
        $this->assertEquals(0, $builder->get());
    }

    public static function constructorWithNonIntegerProvider()
    {
        return array(
            array(234.463),
            array('asdgasdf'),
            array(array()),
            array(new \stdClass()),
        );
    }

    public static function addAndRemoveProvider()
    {
        return array(
            array('VIEW_BASIC', MaskBuilder::MASK_VIEW_BASIC),
            array('CREATE_BASIC', MaskBuilder::MASK_CREATE_BASIC),
            array('EDIT_BASIC', MaskBuilder::MASK_EDIT_BASIC),
            array('DELETE_BASIC', MaskBuilder::MASK_DELETE_BASIC),
            array('ASSIGN_BASIC', MaskBuilder::MASK_ASSIGN_BASIC),
            array('SHARE_BASIC', MaskBuilder::MASK_SHARE_BASIC),
            array('VIEW_LOCAL', MaskBuilder::MASK_VIEW_LOCAL),
            array('CREATE_LOCAL', MaskBuilder::MASK_CREATE_LOCAL),
            array('EDIT_LOCAL', MaskBuilder::MASK_EDIT_LOCAL),
            array('DELETE_LOCAL', MaskBuilder::MASK_DELETE_LOCAL),
            array('ASSIGN_LOCAL', MaskBuilder::MASK_ASSIGN_LOCAL),
            array('SHARE_LOCAL', MaskBuilder::MASK_SHARE_LOCAL),
            array('VIEW_DEEP', MaskBuilder::MASK_VIEW_DEEP),
            array('CREATE_DEEP', MaskBuilder::MASK_CREATE_DEEP),
            array('EDIT_DEEP', MaskBuilder::MASK_EDIT_DEEP),
            array('DELETE_DEEP', MaskBuilder::MASK_DELETE_DEEP),
            array('ASSIGN_DEEP', MaskBuilder::MASK_ASSIGN_DEEP),
            array('SHARE_DEEP', MaskBuilder::MASK_SHARE_DEEP),
            array('VIEW_GLOBAL', MaskBuilder::MASK_VIEW_GLOBAL),
            array('CREATE_GLOBAL', MaskBuilder::MASK_CREATE_GLOBAL),
            array('EDIT_GLOBAL', MaskBuilder::MASK_EDIT_GLOBAL),
            array('DELETE_GLOBAL', MaskBuilder::MASK_DELETE_GLOBAL),
            array('ASSIGN_GLOBAL', MaskBuilder::MASK_ASSIGN_GLOBAL),
            array('SHARE_GLOBAL', MaskBuilder::MASK_SHARE_GLOBAL),
        );
    }
}
