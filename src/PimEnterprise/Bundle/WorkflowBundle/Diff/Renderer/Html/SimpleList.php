<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Diff\Renderer\Html;

class SimpleList extends \Diff_Renderer_Html_Array
{
    public function render()
    {
        $changes = parent::render();
        $html = '';
        if (empty($changes)) {
            return $html;
        }

        $html .= '<ul class="diff">';
        foreach ($changes as $i => $blocks) {
            if ($i > 0) {
                $html .= '<li>...</li>';
            }

            foreach ($blocks as $change) {

                foreach ($change['base']['lines'] as $line) {
                    $html .= sprintf('<li class="base %s">%s</li>', $change['tag'], $line);
                }

                foreach ($change['changed']['lines'] as $line) {
                    $html .= sprintf('<li class="changed %s">%s</li>', $change['tag'], $line);
                }

            }
        }
        $html .= '</ul>';

        return $html;
    }
}
