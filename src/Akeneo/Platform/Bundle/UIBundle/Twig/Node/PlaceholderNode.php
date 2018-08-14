<?php
namespace Akeneo\Platform\Bundle\UIBundle\Twig\Node;

use \Twig_Compiler;
use \Twig_Node_Expression_Constant;
use \Twig_Node_Expression_Function;
use \Twig_Node_Include;
use \Twig_Node_Print;

class PlaceholderNode extends \Twig_Node
{
    /**
     * @var array Array with placeholder data
     */
    protected $placeholder;

    protected $variables;

    protected $wrapClassName;

    /**
     * @param array $placeholder Array with placeholder data
     * @param       $variables Additional placeholder data
     * @param string $wrapClassName css class name for items wrapper
     * @param int   $line Line
     * @param int   $tag twig tag
     */
    public function __construct(array $placeholder, $variables, $wrapClassName, $line, $tag)
    {
        $items = isset($placeholder['items']) ?: [];
        parent::__construct([], ['value' => $items], $line, $tag);
        $this->placeholder = $placeholder;
        $this->wrapClassName = $wrapClassName;
        $this->variables = $variables;
    }

    /**
     * {@inheritDoc}
     */
    public function compile(Twig_Compiler $compiler)
    {
        /*if (isset($this->placeholder['label'])) {
            $compiler
                ->write('echo \'<div>\';')
                ->write('echo $this->env->getExtension(\'translator\')->getTranslator()
                    ->trans("' . $this->placeholder['label'] . '");')
                ->write("echo '</div>';\n")
            ;
        }*/
        if (isset($this->placeholder['items']) && count($this->placeholder['items'])) {
            foreach ($this->placeholder['items'] as $item) {
                //$compiler->raw(
                //    'echo \'<div id = "block-' . $blockData['name'] . '" class="' . $this->wrapClassName . '" >\';'
                //);
                if (array_key_exists('template', $item)) {
                    $expression = new Twig_Node_Expression_Constant($item['template'], $this->lineno);
                    $block = new Twig_Node_Include($expression, $this->variables, false, false, $this->lineno, $this->tag);
                    $block->compile($compiler);
                } elseif (array_key_exists('action', $item)) {
                    $expression = new Twig_Node_Expression_Constant($item['action'], $this->lineno);
                    $attr = new Twig_Node_Expression_Constant([], $this->lineno);
                    if ($this->variables === null) {
                        $attributes = $attr;
                    } else {
                        $attributes = $this->variables;
                    }

                    // {{ render(controller('Bundle:Directory:controllerAction', { action: attributes })) }}
                    $controllerFunctionExpression = new Twig_Node_Expression_Function(
                        'controller',
                        new \Twig_Node([$expression, $attributes]),
                        $this->lineno
                    );
                    $renderFunctionExpression = new Twig_Node_Expression_Function(
                        'render',
                        new \Twig_Node(['uri' => $controllerFunctionExpression]),
                        $this->lineno
                    );

                    $block = new Twig_Node_Print($renderFunctionExpression, $this->lineno, $this->tag);
                    $block->compile($compiler);
                }
                //$compiler->raw('echo \'</div>\';');
            }
        }
    }
}
