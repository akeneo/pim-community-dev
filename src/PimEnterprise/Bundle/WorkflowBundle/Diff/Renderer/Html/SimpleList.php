<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Diff\Renderer\Html;

/**
 * HTML list-based diff renderer
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class SimpleList extends \Diff_Renderer_Html_Array
{
    /**
     * Render a diff in a HTML list (<ul>) element
     *
     * @return string
     */
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
