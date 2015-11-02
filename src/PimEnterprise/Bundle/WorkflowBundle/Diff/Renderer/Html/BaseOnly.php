<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Diff\Renderer\Html;

/**
 * Render only original text (the "Original" row in a proposal diff)
 *
 * @author Clement Gautier <clement.gautier@akeneo.com>
 */
class BaseOnly extends \Diff_Renderer_Html_Array
{
    /**
     * {@inheritdoc}
     */
    public function render()
    {
        $changes = parent::render();

        if (empty($changes)) {
            return '';
        }

        $lines = [];
        foreach ($changes as $blocks) {
            foreach ($blocks as $change) {
                foreach ($change['base']['lines'] as $line) {
                    $lines[] = (string) $line;
                }
            }
        }

        return join('<br />', $lines);
    }
}
