<?php

namespace Pim\Bundle\EnrichBundle\ViewElement;

/**
 * Interface for protected view elements that are only displayed if some condition is met
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProtectedViewElementInterface extends ViewElementInterface
{
    /**
     * Indicates whether the view element should be displayed or not
     *
     * @param array $context The twig context
     *
     * @return boolean
     */
    public function isVisible(array $context = []);

    /**
     * Returns ACL resources that have to be granted to display this view element
     *
     * @param array $context The twig context
     *
     * @return array
     */
    public function getAclResources(array $context = []);

    /**
     * Add a new visibility checker to the view element
     *
     * @param ViewElementVisibilityCheckerInterface $checker
     *
     * @return ProtectedViewElementInterface
     */
    public function addVisibilityChecker(ViewElementVisibilityCheckerInterface $checker);

    /**
     * Set the visibility checkers to the view element
     *
     * @param ViewElementVisibilityCheckerInterface[] $checkers
     *
     * @return ProtectedViewElementInterface
     */
    public function setVisibilityCheckers(array $checkers);
}
