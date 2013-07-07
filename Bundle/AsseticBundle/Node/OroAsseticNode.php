<?php

namespace Oro\Bundle\AsseticBundle\Node;

use Assetic\Asset\AssetInterface;

class OroAsseticNode extends \Twig_Node
{

    /**
     * @var AssetInterface
     */
    protected $compressedAsset;

    /**
     * @var AssetInterface
     */
    protected $unCompressAsset;

    protected $nameCompress;
    protected $nameUnCompress;

    public function getUnCompressAsset()
    {
        return $this->unCompressAsset;
    }

    public function getCompressAsset()
    {
        return $this->compressedAsset;
    }

    public function getNameUnCompress()
    {
        return $this->nameUnCompress;
    }

    public function __construct(
        AssetInterface $compressAsset,
        $nameUnCompress,
        AssetInterface $unCompressAsset,
        $nameCompress,
        $filters,
        $inputs,
        \Twig_NodeInterface $body,
        array $attributes = array(),
        $lineno = 0,
        $tag = null)
    {
        $this->nameCompress = $nameCompress;
        $this->nameUnCompress = $nameUnCompress;
        $this->compressedAsset = $compressAsset;
        $this->unCompressAsset = $unCompressAsset;

        $nodes = array('body' => $body);

        $attributes = array_replace(
            array('debug' => null, 'combine' => null, 'var_name' => 'asset_url'),
            $attributes,
            array('inputs' => $inputs, 'filters' => $filters)
        );

        $this->nodes = $nodes;
        $this->attributes = $attributes;
        $this->lineno = $lineno;
        $this->tag = $tag;
    }

    public function compile(\Twig_Compiler $compiler)
    {
        $compiler->addDebugInfo($this);


        $this->compileAsset($compiler, $this->compressedAsset, $this->nameCompress);
        $this->compileDebug($compiler);

        $compiler
            ->write('unset($context[')
            ->repr($this->getAttribute('var_name'))
            ->raw("]);\n")
        ;
    }


    protected function compileDebug(\Twig_Compiler $compiler)
    {
        $i = 0;
        foreach ($this->unCompressAsset as $leaf) {
            $leafName = $this->nameUnCompress.'_'.$i++;
            $this->compileAsset($compiler, $leaf, $leafName);
        }
    }

    protected function compileAsset(\Twig_Compiler $compiler, AssetInterface $asset, $name)
    {
        $compiler
            ->write("// asset \"$name\"\n")
            ->write('$context[')
            ->repr($this->getAttribute('var_name'))
            ->raw('] = ')
        ;

        if ($this->compressedAsset == $asset) {
            $this->compileCombineDebugAssetUrl($compiler, $asset, $name);
        } else {
            $this->compileAssetUrl($compiler, $asset, $name);
        }

        $compiler
            ->raw(";\n")
            ->subcompile($this->getNode('body'))
        ;
    }

    protected function compileCombineDebugAssetUrl(\Twig_Compiler $compiler, AssetInterface $asset, $name)
    {
        $compiler->raw('$this->env->getExtension(\'assets\')->getAssetUrl(')
            ->repr($asset->getTargetPath())
            ->raw(')');


    }

    protected function compileAssetUrl(\Twig_Compiler $compiler, AssetInterface $asset, $name)
    {

        $compiler->subcompile($this->getPathFunction($name));
    }

    private function getPathFunction($name)
    {
        return new \Twig_Node_Expression_Function(
            version_compare(\Twig_Environment::VERSION, '1.2.0-DEV', '<')
                ? new \Twig_Node_Expression_Name('path', $this->getLine()) : 'path',
            new \Twig_Node(array(new \Twig_Node_Expression_Constant('_assetic_'.$name, $this->getLine()))),
            $this->getLine()
        );
    }
}