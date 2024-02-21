<?php

namespace Akeneo\Platform\Bundle\UIBundle\ViewElement;

use Akeneo\Platform\Bundle\UIBundle\ViewElement\Checker\VisibilityCheckerInterface;

/**
 * View element interface
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ViewElementInterface
{
    /**
     * Get the view element alias
     *
     * @return string
     */
    public function getAlias();

    /**
     * Get the template
     *
     * @return string
     */
    public function getTemplate();

    /**
     * Get additional template parameters
     *
     * @param array $context The twig context
     *
     * @return array
     */
    public function getParameters(array $context = []);

    /**
     * Indicates whether the view element should be displayed in the given context
     *
     * @param array $context The twig context
     *
     * @return bool
     */
    public function isVisible(array $context = []);

    /**
     * Add a new visibility checker to the view element
     *
     * @param VisibilityCheckerInterface $checker
     * @param array                      $config
     *
     * @return ViewElementInterface
     */
    public function addVisibilityChecker(VisibilityCheckerInterface $checker, array $config = []);

    /**
     * Set the visibility checkers to the view element
     *
     * @param VisibilityCheckerInterface[] $checkers
     *
     * @return ViewElementInterface
     */
    public function setVisibilityCheckers(array $checkers);
}
