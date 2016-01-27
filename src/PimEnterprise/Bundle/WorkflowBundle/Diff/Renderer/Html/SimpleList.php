<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Diff\Renderer\Html;

/**
 * HTML list-based diff renderer
 *
 * @author Gildas Quemener <gildas@akeneo.com>
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
        $result = ['before' => [], 'after' => []];

        foreach ($changes as $blocks) {
            foreach ($blocks as $change) {
                $before = $change['base']['lines'];
                $after = $change['changed']['lines'];

                $result['before'][] = is_array($before) ? implode(', ', $before) : $before;
                $result['after'][]  = is_array($after) ? implode(', ', $after) : $after;
            }
        }

        $result['before'] = implode(', ', array_filter($result['before']));
        $result['after'] = implode(', ', array_filter($result['after']));

        return $result;
    }
}
