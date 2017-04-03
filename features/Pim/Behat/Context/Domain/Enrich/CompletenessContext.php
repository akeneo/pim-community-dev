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
        $link = $this->spin(function () use ($attribute, $locale, $channel) {
            return $this->getCurrentPage()->getElement('Completeness')->find(
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
     * @param string $code
     * @param string $label
     *
     * @Then /^The label for the "([^"]*)" channel for "([^"]*)" locale should be "([^"]*)"$/
     */
    public function theCurrentLocaleChannelLabelShouldBe($code, $locale, $label)
    {
        $this->spin(function () use ($code, $locale, $label) {
            $completeness = $this->getCurrentPage()->getElement('Completeness');
            $data = $this->getCurrentPage()->getElement('Completeness')->getCompletenessData();
            $channelLabel = $data[$locale]['data'][$code]['label'];

            return $channelLabel === $label;
        }, sprintf('"%s" does not have the label "%s".', $code, $label));
    }

    /**
     * @param string $localeCode en_US, fr_FR, etc.
     * @param int    $position
     *
     * @Given /^I should see the "([^"]*)" completeness in position ([0-9]+)$/
     */
    public function iShouldSeeTheCompletenessInPositionNth($localeCode, $position)
    {
        $this->spin(function () use ($localeCode, $position) {
            $completenessData = $this->getCurrentPage()->getElement('Completeness')->getCompletenessData();

            return (int) $position === $completenessData[$localeCode]['position'];
        }, sprintf(
            '"%s" completeness not found in position %s in tab.',
            $localeCode,
            $position
        ));
    }

    /**
     * @param string $localeCode
     *
     * @Given /^The completeness "([^"]*)" should be (closed|opened)$/
     */
    public function theCompletenessShouldBeOpenedOrClosed($localeCode, $state)
    {
        $isOpened = ('opened' === $state);

        $this->spin(function () use ($localeCode, $isOpened) {
            $completenessData = $this->getCurrentPage()->getElement('Completeness')->getCompletenessData();

            return $isOpened === $completenessData[$localeCode]['opened'];
        }, sprintf('Expected to see "%s" completeness %s. But it was not', $localeCode, $state));
    }

    /**
     * @param TableNode $table
     *
     * @Then /^I should see the completeness:$/
     *
     * @throws ExpectationException
     */
    public function iShouldSeeTheCompleteness(TableNode $table)
    {
        $table = $table->getHash();

        $this->spin(function () use ($table) {
            $completenessData = $this->convertStructuredToFlat(
                $this->getCurrentPage()->getElement('Completeness')->getCompletenessData()
            );

            foreach ($table as $index => $expected) {
                if (isset($expected['missing_values'])) {
                    // Expected missing values need to be converted to array to be compared
                    $expected['missing_values'] = '' !== $expected['missing_values'] ?
                        explode(', ', $expected['missing_values']) :
                        [];
                }

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
        foreach ($structuredCompleteness as $localeCode => $localeBlock) {
            foreach ($localeBlock['data'] as $scopeCode => $scopeBlock) {
                $flatCompleteness[] = [
                    'channel'        => $scopeCode,
                    'locale'         => $localeCode,
                    'state'          => $scopeBlock['state'],
                    'missing_values' => array_values($scopeBlock['missing_values']),
                    'ratio'          => $scopeBlock['ratio']
                ];
            }
        }

        return $flatCompleteness;
    }
}
