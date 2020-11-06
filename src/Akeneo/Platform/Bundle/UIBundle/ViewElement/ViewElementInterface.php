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
     */
    public function getAlias(): string;

    /**
     * Get the template
     */
    public function getTemplate(): string;

    /**
     * Get additional template parameters
     *
     * @param array $context The twig context
     */
    public function getParameters(array $context = []): array;

    /**
     * Indicates whether the view element should be displayed in the given context
     *
     * @param array $context The twig context
     */
    public function isVisible(array $context = []): bool;

    /**
     * Add a new visibility checker to the view element
     *
     * @param VisibilityCheckerInterface $checker
     * @param array                      $config
     */
    public function addVisibilityChecker(VisibilityCheckerInterface $checker, array $config = []): \Akeneo\Platform\Bundle\UIBundle\ViewElement\ViewElementInterface;

    /**
     * Set the visibility checkers to the view element
     *
     * @param VisibilityCheckerInterface[] $checkers
     */
    public function setVisibilityCheckers(array $checkers): \Akeneo\Platform\Bundle\UIBundle\ViewElement\ViewElementInterface;
}
