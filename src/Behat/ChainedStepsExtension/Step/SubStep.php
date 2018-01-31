<?php

/*
 * This file is part of the Behat ChainedStepsExtension.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\ChainedStepsExtension\Step;

use Behat\Gherkin\Node\StepNode;

/**
 * Base substep class.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class SubStep extends StepNode
{
    /**
     * @var string
     */
    private $language;

    /**
     * Initializes sub-step.
     *
     * @param string $type
     * @param string $text
     * @param array  $arguments
     */
    public function __construct($type, $text, array $arguments)
    {
        parent::__construct('Given', $text, $arguments, 0);
    }

    /**
     * Sets language.
     *
     * @param string $language
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }

    /**
     * Returns language.
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }
}
