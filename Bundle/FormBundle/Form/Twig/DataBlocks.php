<?php

namespace Oro\Bundle\FormBundle\Form\Twig;

use Oro\Bundle\FormBundle\Config\SubBlockConfig;
use Symfony\Component\Form\FormView;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

use Oro\Bundle\FormBundle\Config\BlockConfig;
use Oro\Bundle\FormBundle\Config\FormConfig;

class DataBlocks
{
    /**
     * @var FormConfig
     */
    protected $formConfig;

    /**
     * @var string
     */
    protected $formVariableName;

    /**
     * @var mixed
     */
    protected $context;

    /**
     * @var \Twig_Environment
     */
    protected $env;

    /**
     * @var PropertyAccessor
     */
    protected $accessor;

    public function __construct()
    {
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * @param  \Twig_Environment $env
     * @param                    $context
     * @param  FormView          $form
     * @param  string            $formVariableName
     * @return array
     */
    public function render(\Twig_Environment $env, $context, FormView $form, $formVariableName = 'form')
    {
        $this->formVariableName = $formVariableName;
        $this->formConfig       = new FormConfig;
        $this->context          = $context;
        $this->env              = $env;

        $tmpLoader = $env->getLoader();
        $env->setLoader(new \Twig_Loader_Chain(array($tmpLoader, new \Twig_Loader_String())));

        $this->renderBlock($form);

        $env->setLoader($tmpLoader);

        return $this->formConfig->toArray();
    }

    /**
     * @param FormView $form
     */
    protected function renderBlock(FormView $form)
    {
        if (isset($form->vars['block_config'])) {
            foreach ($form->vars['block_config'] as $code => $blockConfig) {
                $this->createBlock($code, $blockConfig);
            }
        }

        foreach ($form->children as $name => $child) {
            if (isset($child->vars['block']) || isset($child->vars['subblock'])) {

                $block = null;
                if ($this->formConfig->hasBlock($child->vars['block'])) {
                    $block = $this->formConfig->getBlock($child->vars['block']);
                }

                if (!$block) {
                    $blockCode = $child->vars['block'];
                    $block     = $this->createBlock($blockCode);

                    $this->formConfig->addBlock($block);
                }

                $subBlock = $this->getSubBlock($name, $child, $block);

                $tmpChild = $child;
                $formPath = '';

                while ($tmpChild->parent) {
                    $formPath = sprintf('.children[\'%s\']', $tmpChild->vars['name']) . $formPath;
                    $tmpChild = $tmpChild->parent;
                }

                $subBlock->addData(
                    $this->env->render(
                        '{{ form_row(' . $this->formVariableName . $formPath . ') }}',
                        $this->context
                    )
                );
            }

            $this->renderBlock($child);
        }
    }

    protected function getSubBlock($name, FormView $child, BlockConfig $block)
    {
        $subBlock = null;
        if (isset($child->vars['subblock']) && $block->hasSubBlock($child->vars['subblock'])) {
            $subBlock = $block->getSubBlock($child->vars['subblock']);
        } elseif (!isset($child->vars['subblock'])) {
            $subBlocks = $block->getSubBlocks();
            $subBlock  = reset($subBlocks);
        }

        if (!$subBlock) {
            if (isset($child->vars['subblock'])) {
                $subBlockCode = $child->vars['subblock'];
            } else {
                $subBlockCode = $name . '__subblock';
            }

            $subBlock = $this->createSubBlock($subBlockCode, array('title' => null));
            $block->addSubBlock($subBlock);
        }

        return $subBlock;
    }

    /**
     * @param        $code
     * @param  array $blockConfig
     * @return BlockConfig
     */
    protected function createBlock($code, $blockConfig = array())
    {
        if ($this->formConfig->hasBlock($code)) {
            $block = $this->formConfig->getBlock($code);
        } else {
            $block = new BlockConfig($code);
        }
        $block->setClass($this->accessor->getValue($blockConfig, '[class]'));
        $block->setPriority($this->accessor->getValue($blockConfig, '[priority]'));

        $title = $this->accessor->getValue($blockConfig, '[title]')
            ? $this->accessor->getValue($blockConfig, '[title]')
            : ucfirst($code);
        $block->setTitle($title);
        $block->setDescription($this->accessor->getValue($blockConfig, '[description]'));

        foreach ((array)$this->accessor->getValue($blockConfig, '[subblocks]') as $subCode => $subBlockConfig) {
            $block->addSubBlock($this->createSubBlock($subCode, (array)$subBlockConfig));
        }

        $this->formConfig->addBlock($block);

        return $block;
    }

    /**
     * @param $code
     * @param $config
     * @return SubBlockConfig
     */
    protected function createSubBlock($code, $config)
    {
        $subBlock = new SubBlockConfig($code);
        $subBlock->setTitle($this->accessor->getValue($config, '[title]'));
        $subBlock->setPriority($this->accessor->getValue($config, '[priority]'));
        $subBlock->setDescription($this->accessor->getValue($config, '[description]'));
        $subBlock->setUseSpan($this->accessor->getValue($config, '[useSpan]'));

        return $subBlock;
    }
}
