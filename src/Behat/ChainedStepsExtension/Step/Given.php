<?php

/*
 * This file is part of the Behat ChainedStepsExtension.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\ChainedStepsExtension\Step;

/**
 * `Given` sub-step.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Given extends SubStep
{
    /**
     * Initializes `Given` sub-step.
     */
    public function __construct()
    {
        $arguments = func_get_args();
        $text = array_shift($arguments);

        parent::__construct('Given', $text, $arguments);
    }
}
