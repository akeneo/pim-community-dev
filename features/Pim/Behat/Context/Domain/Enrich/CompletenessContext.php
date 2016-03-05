<?php

namespace Pim\Behat\Context\Domain\Enrich;

use Behat\Mink\Exception\ExpectationException;
use Pim\Behat\Context\PimContext;

/**
 * This context is about to test the behavior of the completeness
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessContext extends PimContext
{
    /**
     * @param string $locale   en_US, fr_FR, etc.
     * @param int    $position
     *
     * @Given /^I should see the "([^"]*)" completeness in position ([0-9]+)$/
     */
    public function iShouldSeeTheCompletenessInPositionNth($locale, $position)
    {
        $completeness = $this->getCurrentPage()->getElement('Completeness')->findNthCompleteness($position);
        if ($completeness->getLocale() !== $locale) {
            throw new ExpectationException(
                sprintf(
                    '"%s" completeness found in position %s in tab. "%s" expected.',
                    $completeness->getLocale(),
                    $position,
                    $locale
                ),
                $this->getSession()
            );
        }
    }

    /**
     * @param string $locale
     *
     * @Given /^The completeness "([^"]*)" should be (closed|opened)$/
     */
    public function theCompletenessShouldBeOpenedOrClosed($locales, $state)
    {
        $completenessPanel = $this->getCurrentPage()->getElement('Completeness');

        $locales = explode(',', $locales);
        foreach ($locales as $locale) {
            $locale = trim($locale);

            $completeness = $completenessPanel->findCompletenessForLocale($locale);
            $condition    = 'closed' === $state ? 'true' : 'false';
            if ($completeness->getState() !== $condition) {
                throw new ExpectationException(
                    sprintf('Expected to see "%s" completeness %s.', $locale, $state),
                    $this->getSession()
                );
            }
        }
    }
}
