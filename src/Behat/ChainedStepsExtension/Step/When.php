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
 * `When` substep.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class When extends SubStep
{
    /**
     * Initializes `When` sub-step.
     */
    public function __construct()
    {
        $arguments = func_get_args();
        $text = array_shift($arguments);

        parent::__construct('When', $text, $arguments);
    }
}
