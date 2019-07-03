<?php

namespace Pim\Behat\Context\Domain\Enrich;

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\ExpectationException;
use Context\Spin\SpinCapableTrait;
use Context\Spin\SpinException;
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
    use SpinCapableTrait;

    /**
     * @param string $attribute
     * @param string $locale
     * @param string $channel
     *
     * @Then /^I click on the missing "([^"]*)" value for "([^"]*)" locale and "([^"]*)" channel/
     */
    public function iClickOnTheMissingValueForLocaleAndChannel($attribute, $locale, $channel)
    {
        $completenessElement = $this->getElementOnCurrentPage('Completeness');

        $link = $this->spin(function () use ($attribute, $locale, $channel, $completenessElement) {
            return $completenessElement->find(
                'css',
                sprintf(
                    '.missing-attributes [data-attribute="%s"][data-locale="%s"][data-channel="%s"]',
                    $attribute,
                    $locale,
                    $channel
                )
            );
        }, sprintf("Can't find missing '%s' value link for %s/%s", $attribute, $locale, $channel));

        $link->click();
    }

    /**
     * @param string $attribute
     * @param string $locale
     *
     * @Then /^I click on the missing "([^"]*)" value for "([^"]*)" locale/
     */
    public function iClickOnTheMissingValueForLocale($attribute, $locale)
    {
        $completenessElement = $this->getElementOnCurrentPage('Completeness dropdown');

        $link = $this->spin(function () use ($attribute, $locale, $completenessElement) {
            return $completenessElement->find(
                'css',
                sprintf(
                    '.missing-attribute[data-attribute="%s"][data-locale="%s"]',
                    $attribute,
                    $locale
                )
            );
        }, sprintf("Can't find missing '%s' value link for %s", $attribute, $locale));

        $link->click();
    }

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
     * @param string $channelCode ecommerce, mobile...
     * @param int    $position
     *
     * @Given /^I should see the "([^"]*)" completeness in position ([0-9]+)$/
     */
    public function iShouldSeeTheCompletenessInPositionNth($channelCode, $position)
    {
        $completeness = $this->getElementOnCurrentPage('Completeness');

        $this->spin(function () use ($channelCode, $position, $completeness) {
            $completenessData = $completeness->getCompletenessData();

            return (int) $position === $completenessData[$channelCode]['position'];
        }, sprintf(
            '"%s" completeness not found in position %s in tab.',
            $channelCode,
            $position
        ));
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

        $completeness = $this->getElementOnCurrentPage('Completeness');

        $this->spin(function () use ($table, $completeness) {
            $completenessData = $this->convertStructuredToFlat($completeness->getCompletenessData());

            foreach ($table as $index => $expected) {
                // This allows to omit columns in the table
                $expected = array_merge($completenessData[$index], $expected);

                if ($completenessData[$index] !== $expected ||
                    $completenessData[$index]['missing_values'] !== $expected['missing_values']
                ) {
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
     * Convert the completeness data from DOM structure to flat format to ease comparison.
     *
     * Input:
     *
     * [
     *     'en_US' => [
     *          'opened'   => true,
     *          'position' => 1,
     *          'data'     => [
     *              'mobile' => [
     *                  'ratio'          => '33%',
     *                  'state'          => 'warning',
     *                  'missing_values' => [
     *                      'price' => 'Price',
     *                      'size'  => 'Size',
     *                  ],
     *              ],
     *              'tablet' => [
     *                  'ratio'          => '100%',
     *                  'state'          => 'success',
     *                  'missing_values' => [],
     *              ],
     *          ],
     *      ],
     * ]
     *
     * Output:
     *
     * [
     *     [
     *         'channel'        => 'mobile',
     *         'locale'         => 'en_US',
     *         'state'          => 'warning',
     *         'missing_values' => [
     *             'Price',
     *             'Size',
     *          ],
     *          'ratio' => '33%',
     *     ],
     *     [
     *         'channel'        => 'tablet',
     *         'locale'         => 'en_US',
     *         'state'          => 'success',
     *         'missing_values' => [],
     *         'ratio'          => '100%',
     *     ]
     * ]
     *
     * @param array $structuredCompleteness
     *
     * @return array
     */
    protected function convertStructuredToFlat(array $structuredCompleteness)
    {
        $flatCompleteness = [];
        foreach ($structuredCompleteness as $scopeCode => $scopeBlock) {
            foreach ($scopeBlock['data'] as $localeCode => $localeBlock) {
                $flatCompleteness[] = [
                    'channel'        => $scopeCode,
                    'locale'         => $localeCode,
                    'state'          => $localeBlock['state'],
                    'missing_values' => $localeBlock['missing_values'],
                    'ratio'          => $localeBlock['ratio']
                ];
            }
        }

        return $flatCompleteness;
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

            return strtolower($badgeAmount) === strtolower($completenessAmount);
        }, sprintf('The completeness badge does not show the amount "%s".', $completenessAmount));
    }
}
