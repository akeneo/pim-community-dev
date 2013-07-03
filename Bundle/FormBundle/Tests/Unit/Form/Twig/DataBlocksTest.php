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



//        $datablocks_extension = new DataBlocks($this->getContainerMock());
//        $this->twigExtension = $datablocks_extension;
//
//
//        $loader = new \Twig_Loader_String();
//        $this->twig = new \Twig_Environment($loader, array(
//            'debug' => true,
//            'cache' => false,
//            'autoescape' => false,
//        ));
//
//        $this->twig->addExtension($this->twigExtension);
    }


    public function testConstruct()
    {
        $this->assertInstanceOf(
            'Symfony\Component\PropertyAccess\PropertyAccessor',
            $this->readAttribute($this->datablocks, 'accessor')
        );
    }

    public function testRender()
    {
//        $this->assertEquals(
//            '',
//            $this->twig->render("")
//        );
    }

    /**
     * @param \Twig_Environment $env
     * @param                   $context
     * @param FormView          $form
     * @param string            $formVariableName
     * @return array
     */
//    public function render(\Twig_Environment $env, $context, FormView $form, $formVariableName = 'form')
//    {
//        $this->formVariableName = $formVariableName;
//        $this->formConfig       = new FormConfig;
//        $this->context          = $context;
//        $this->env              = $env;
//
//        $tmpLoader = $env->getLoader();
//        $env->setLoader(new \Twig_Loader_String());
//
//        try {
//
//            $this->renderBlock($form);
//        } catch (\Exception $e) {
//            var_dump($e);
//            die;
//        }
//
//        $env->setLoader($tmpLoader);
//
//        return $this->formConfig->toArray();
//    }


//    protected function createSubBlockTest($code, $config)
//    {
//        $subBlock = new SubBlockConfig($code);
//        $subBlock->setTitle($this->accessor->getValue($config, '[title]'));
//        $subBlock->setPriority($this->accessor->getValue($config, '[priority]'));
//
//        return $subBlock;
//    }
}