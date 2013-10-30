<?php

namespace Oro\Bundle\FormBundle\Tests\Unit\Form\Twig;

use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\Forms;

use Oro\Bundle\FormBundle\Form\Extension\DataBlockExtension;
use Oro\Bundle\FormBundle\Form\Twig\DataBlocks;

class DataBlocksTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @var FormFactory
     */
    private $factory;

    /**
     * @var  DataBlocks
     */
    private $dataBlocks;

    /**
     * @var array
     */
    private $testFormConfig = array(
        'second' => array(
            'title'       => 'Second',
            'class'       => null,
            'subblocks'   => array(
                'text_3__subblock' => array(
                    'code'        => 'text_3__subblock',
                    'title'       => null,
                    'data'        => array(null),
                    'description' => null,
                    'useSpan'     => true
                ),
            ),
            'description' => null
        ),
        'first'  => array(
            'title'       => 'First Block',
            'class'       => null,
            'subblocks'   => array(
                'first'  => array(
                    'code'        => 'first',
                    'title'       => null,
                    'data'        => array(null),
                    'description' => null,
                    'useSpan'     => true
                ),
                'second' => array(
                    'code'        => 'second',
                    'title'       => 'Second SubBlock',
                    'data'        => array(null),
                    'description' => null,
                    'useSpan'     => true
                ),
            ),
            'description' => 'some desc'
        ),
        'third'  => array(
            'title'       => 'Third',
            'class'       => null,
            'subblocks'   => array(
                'text_4__subblock' => array(
                    'code'        => 'text_4__subblock',
                    'title'       => null,
                    'data'        => array(null),
                    'description' => null,
                    'useSpan'     => true
                ),
                'first'            => array(
                    'code'        => 'first',
                    'title'       => null,
                    'data'        => array(null),
                    'description' => null,
                    'useSpan'     => true
                ),
            ),
            'description' => null
        ),
    );

    public function setUp()
    {
        $this->dataBlocks = new DataBlocks();

        $this->factory = Forms::createFormFactoryBuilder()
            ->addTypeExtension(new DataBlockExtension())
            ->getFormFactory();

        $this->twig = $this->getMockBuilder('\Twig_Environment')
            ->disableOriginalConstructor()
            ->getMock();

        $this->twig->expects($this->any())
            ->method('render')
            ->will($this->returnValue(null));
        $this->twig->expects($this->any())
            ->method('getLoader')
            ->will($this->returnValue($this->getMockForAbstractClass('\Twig_LoaderInterface')));
    }

    public function testConstruct()
    {
        $this->assertInstanceOf(
            'Symfony\Component\PropertyAccess\PropertyAccessor',
            $this->readAttribute($this->dataBlocks, 'accessor')
        );
    }

    public function testRender()
    {
        $options = array(
            'block_config' =>
                array(
                    'first'  => array(
                        'priority'    => 1,
                        'title'       => 'First Block',
                        'subblocks'   => array(
                            'first'  => array(),
                            'second' => array(
                                'title' => 'Second SubBlock'
                            ),
                        ),
                        'description' => 'some desc'
                    ),
                    'second' => array(
                        'priority' => 2,
                    )
                )
        );
        $builder = $this->factory->createNamedBuilder('test', 'form', null, $options);
        $builder->add('text_1', null, array('block' => 'first', 'subblock' => 'second'));
        $builder->add('text_2', null, array('block' => 'first'));
        $builder->add('text_3', null, array('block' => 'second'));
        $builder->add('text_4', null, array('block' => 'third'));
        $builder->add('text_5', null, array('block' => 'third', 'subblock' => 'first'));
        $builder->add('text_6', null);

        $formView = $builder->getForm()->createView();

        $result = $this->dataBlocks->render($this->twig, array('form' => $formView), $formView);

        $this->assertEquals($this->testFormConfig, $result);
    }
}
