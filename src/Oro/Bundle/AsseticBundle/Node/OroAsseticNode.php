<?php

namespace Oro\Bundle\AsseticBundle\Node;

use Assetic\Asset\AssetInterface;

class OroAsseticNode extends \Twig_Node
{
    /** @var AssetInterface[] */
    protected $compressAssets;

    /** @var AssetInterface[] */
    protected $unCompressAssets;

    public function __construct(
        array $assets,
        array $filters,
        array $inputs,
        \Twig_NodeInterface $body,
        array $attributes = [],
        int $lineno = 0,
        ?string $tag = null
    ) {
        $this->compressAssets = $assets['compress'];
        $this->unCompressAssets = $assets['un_compress'];

        $nodes = ['body' => $body];

        $attributes = array_replace(
            ['debug' => null, 'combine' => null, 'var_name' => 'asset_url'],
            $attributes,
            ['inputs' => $inputs, 'filters' => $filters]
        );

        $this->nodes = $nodes;
        $this->attributes = $attributes;
        $this->lineno = $lineno;
        $this->tag = $tag;
    }

    public function getCompressAssets(): array
    {
        return $this->compressAssets;
    }

    public function getUnCompressAssets(): array
    {
        return $this->unCompressAssets;
    }

    public function compile(\Twig_Compiler $compiler)
    {
        $compiler->addDebugInfo($this);

        $isMain = true;
        foreach ($this->compressAssets as $name => $compressAsset) {
            $this->compileMainStylesheetFlag($compiler, $isMain);
            $this->compileCombineDebugAssetUrl($compiler, $compressAsset->getTargetPath(), $name);

            $compiler->write('unset($context[\'isMain\']);'."\n");
            $isMain = false;
        }

        $isMain = true;
        foreach ($this->unCompressAssets as $name => $unCompressAsset) {
            $this->compileMainStylesheetFlag($compiler, $isMain);
            $this->compileDebug($compiler, $unCompressAsset, $name);

            $compiler->write('unset($context[\'isMain\']);'."\n");
            $isMain = false;
        }
    }

    protected function compileDebug(\Twig_Compiler $compiler, AssetInterface $asset, string $name): void
    {
        $inputs = $this->getAttribute('inputs');
        $i = 0;

        foreach ($asset as $leafAsset) {
            $leafName = $name.'_'.$i++;
            if (!in_array($asset->getSourcePath(), $inputs['uncompress'][0])) {
                $this->compileAssetUrl($compiler, $leafName);
            } else {
                $this->compileCombineDebugAssetUrl($compiler, $leafAsset->getSourcePath(), $leafName);
            }
        }
    }

    protected function compileCombineDebugAssetUrl(\Twig_Compiler $compiler, string $path, string $name): void
    {
        $compiler
            ->write("// asset \"$name\"\n")
            ->write('$context[\'name\'] = ')
            ->repr($name)
            ->raw(";\n")
            ->write('$context[')
            ->repr($this->getAttribute('var_name'))
            ->raw('] = ')
            ->raw('$this->env->getExtension(\'asset\')->getAssetUrl(')
            ->repr($path)
            ->raw(')')
            ->raw(";\n")
            ->subcompile($this->getNode('body'))
            ->write('unset($context[\'name\'])'."\n;")
            ->write('unset($context[')
            ->repr($this->getAttribute('var_name'))
            ->raw("]);\n")
        ;
    }

    protected function compileAssetUrl(\Twig_Compiler $compiler, string $name): void
    {
        $compiler
            ->write("// asset \"$name\"\n")
            ->write('$context[\'name\'] = ')
            ->repr($name)
            ->raw(";\n")
            ->write('$context[')
            ->repr($this->getAttribute('var_name'))
            ->raw('] = ')
            ->subcompile(
                new \Twig_Node_Expression_Function(
                    'path',
                    new \Twig_Node([new \Twig_Node_Expression_Constant('_assetic_'.$name, $this->getTemplateLine())]),
                    $this->getTemplateLine()
                )
            )
            ->raw(";\n")
            ->subcompile($this->getNode('body'))
            ->write('unset($context[\'name\'])'."\n;")
            ->write('unset($context[')
            ->repr($this->getAttribute('var_name'))
            ->raw("]);\n")
        ;
    }

    private function compileMainStylesheetFlag(\Twig_Compiler $compiler, bool $isMain): void
    {
        $compiler
            ->write('$context[\'isMain\'] = ')
            ->repr($isMain)
            ->raw(";\n")
        ;
    }
}
