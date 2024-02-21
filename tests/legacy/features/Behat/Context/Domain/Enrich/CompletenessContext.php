<?php

namespace Pim\Behat\Context\Domain\Enrich;

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\ExpectationException;
use Context\Spin\SpinCapableTrait;
use Context\Spin\SpinException;
use Pim\Behat\Context\PimContext;
use Webmozart\Assert\Assert;

/**
 * This context is about to test the behavior of the completeness
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessContext extends PimContext
{
    use SpinCapableTrait;

    /**
     * @param string $code
     * @param string $label
     *
     * @Then /^The label for the "([^"]*)" channel should be "([^"]*)"$/
     */
    public function theCurrentLocaleChannelLabelShouldBe($code, $label)
    {
        $completeness = $this->getElementOnCurrentPage('Completeness');

        $this->spin(function () use ($code, $label, $completeness) {
            $data = $completeness->getCompletenessData();
            $channelLabel = $data[$code]['label'];

            return strtolower($channelLabel) === strtolower($label);
        }, sprintf('"%s" does not have the label "%s".', $code, $label));
    }

    /**
     * @param TableNode $table
     *
     * @Then /^I should see the completeness:$/
     *
     * @throws ExpectationException
     */
    public function iShouldSeeTheCompletenessInThePanel(TableNode $table)
    {
        $table = $table->getHash();

        $textOccurrences  = array_reduce($table, function (array $textOccurrences, array $completeness) {
            $textOccurrences[$completeness['channel']] = 1;
            $textOccurrences[$completeness['locale']] = ($textOccurrences[$completeness['locale']] ?? 0) + 1;
            $textOccurrences[$completeness['ratio']] = ($textOccurrences[$completeness['ratio']] ?? 0) + 1;

            return $textOccurrences;
        }, []);

        $completenessText = strip_tags($this->getElementOnCurrentPage('Completeness')->getHtml());

        foreach ($textOccurrences as $text => $expectedOccurrences) {
            $occurrences = substr_count($completenessText, $text);
            Assert::greaterThanEq($occurrences, $expectedOccurrences, sprintf('Expect to find at least %d occurrence of "%s" in the completeness panels, but got %d', $expectedOccurrences, $text, $occurrences));
        }
    }

    /**
     * @param TableNode $table
     *
     * @Then /^I should see the completeness in the dropdown:$/
     *
     * @throws ExpectationException
     */
    public function iShouldSeeTheCompletenessInTheDropdown(TableNode $table)
    {
        $table = $table->getHash();
        $completeness = $this->getElementOnCurrentPage('Completeness dropdown');

        $this->spin(function () use ($table, $completeness) {
            $completenessData = $completeness->getCompletenessData();

            foreach ($table as $index => $expected) {
                // This allows to omit columns in the table
                $expected = array_merge($completenessData[$index], $expected);
                sort($completenessData[$index]['missing_required_attributes']);

                if (isset($expected['missing_required_attributes'])) {
                    $expected['missing_required_attributes'] = array_map(
                        'trim',
                        explode(',', $expected['missing_required_attributes'])
                    );
                    sort($expected['missing_required_attributes']);
                }

                if ($completenessData[$index] !== $expected) {
                    throw new SpinException(sprintf(
                        'Expected completeness %s does not match %s',
                        var_export($expected, true),
                        var_export($completenessData[$index], true)
                    ));
                }
            }

            return true;
        }, 'Completeness assertion failed');
    }

    /**
     * @param string $completenessAmount
     *
     * @Then /^the completeness badge label should show "([^"]*)"$/
     */
    public function theCompletenessBadgeLabelShouldShow($completenessAmount)
    {
        $this->spin(function () use ($completenessAmount) {
            $badge = $this->getCurrentPage()->find('css', '.completeness-badge');
            $badgeAmount = $badge->getText();

            return strtolower(trim($badgeAmount)) === strtolower($completenessAmount);
        }, sprintf('The completeness badge does not show the amount "%s".', $completenessAmount));
    }
}
