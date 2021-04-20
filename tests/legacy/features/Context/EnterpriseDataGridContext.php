<?php

namespace Context;

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ExpectationException;
use Context\DataGridContext as BaseDataGridContext;

/**
 * Enterprise datagrid context
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class EnterpriseDataGridContext extends BaseDataGridContext
{
    public function __construct(string $mainContextClass)
    {
        $this->mainContextClass = $mainContextClass;
        $this->gridNames = array_merge(
            $this->gridNames,
            [
                'published products' => 'published-product-grid',
                'rules'              => 'rule-grid',
            ]
        );
    }

    /**
     * @param string $elements
     *
     * @throws ExpectationException
     *
     * @Then /^I should see published products? (.*)$/
     */
    public function iShouldSeeEntities($elements)
    {
        parent::iShouldSeeEntities($elements);
    }

    /**
     * @Then /^the cell "([^"]+)" in row "([^"]+)" should contain the thumbnail for channel "([^"]+)"(?: and locale "([^"]+)")?$/
     *
     * @param string      $column
     * @param string      $code
     * @param string      $channelCode
     * @param string|null $localeCode
     */
    public function theCellShouldContainThumbnailForContext($column, $code, $channelCode, $localeCode = null)
    {
        $image = $this->getDatagrid()->getCellImage($column, $code);
        $this->checkCellThumbnail($image, $code, $channelCode, $localeCode);
    }

    /**
     * @Then /^the row "([^"]+)" should contain the thumbnail for channel "([^"]+)"(?: and locale "([^"]+)")?$/
     *
     * @param string      $code
     * @param string      $channelCode
     * @param string|null $localeCode
     */
    public function theRowShouldContainThumbnailForContext($code, $channelCode, $localeCode = null)
    {
        $image = $this->getDatagrid()->getCellImage('thumbnail', $code);
        $this->checkCellThumbnail($image, $code, $channelCode, $localeCode);
    }

    /**
     * Expects table as :
     * | product  | author | attribute  | locale | scope | original | new         |
     * | my-hoody | Mary   | Lace color | en_US  | print |          | Black,White |
     *
     * "locale" and "scope" columns are optional.
     * Note: As values are not ordered you can add multiple values using comma separator.
     *
     * @Given /^I should see the following proposals?:$/
     *
     * @param TableNode $table
     *
     * @throws ExpectationException
     */
    public function iShouldSeeTheFollowingProposals(TableNode $table)
    {
        $datagrid = $this->getDatagrid()->getGrid();

        foreach ($table->getHash() as $hash) {
            $this->spin(function () use ($datagrid, $hash) {
                $selector = '*[data-product="%s"][data-attribute="%s"][data-author="%s"]';
                $params   = [
                    $hash['product'],
                    $hash['attribute'],
                    $hash['author']
                ];

                if (isset($hash['locale']) && '' !== $hash['locale']) {
                    $selector .= '[data-locale="%s"]';
                    $params[] = $hash['locale'];
                }
                if (isset($hash['scope']) && '' !== $hash['scope']) {
                    $selector .= '[data-scope="%s"]';
                    $params[] = $hash['scope'];
                }

                $change = $datagrid->find('css', vsprintf($selector, $params));
                if (null === $change) {
                    return null;
                }

                $original = $change->find('css', '.original-value');
                $new      = $change->find('css', '.new-value');

                $originalExpectedValues = explode(',', trim($hash['original']));
                $newExpectedValues      = explode(',', trim($hash['new']));
                $rawOriginalValues      = explode(', ', $this->getChangeContent($original));
                $rawNewValues           = explode(', ', $this->getChangeContent($new));

                sort($originalExpectedValues);
                sort($newExpectedValues);
                sort($rawOriginalValues);
                sort($rawNewValues);

                $originalExpected = trim(implode(', ', $originalExpectedValues));
                $newExpected      = trim(implode(', ', $newExpectedValues));
                $originalValues   = trim(implode(', ', $rawOriginalValues));
                $newValues        = trim(implode(', ', $rawNewValues));

                if ($originalValues != $originalExpected) {
                    throw $this->createExpectationException(
                        sprintf(
                            'Expected original values to contain "%s", but got "%s".',
                            $originalExpected,
                            $originalValues
                        )
                    );
                }

                if ($newValues != $newExpected) {
                    throw $this->createExpectationException(
                        sprintf(
                            'Expected new values to contain "%s", but got "%s".',
                            $newExpected,
                            $newValues
                        )
                    );
                }

                return true;
            }, 'Proposal data does not match');
        }
    }

    /**
     * @param string $projectLabel
     *
     * @When /^I apply the "([^"]*)" project$/
     */
    public function iApplyTheProject($projectLabel)
    {
        $this->iApplyTheView($projectLabel);
    }

    /**
     * Return the image title if an image is found in the cell, the text content otherwise
     *
     * @param NodeElement $cell
     *
     * @return string
     */
    protected function getChangeContent(NodeElement $cell)
    {
        $img = $cell->find('css', 'img');
        if (null !== $img) {
            return $img->getAttribute('title');
        }

        return $cell->getText();
    }

    /**
     * Check if the specified thumbnail matches a channel and a locale
     *
     * @param NodeElement $image
     * @param string      $code
     * @param string      $channelCode
     * @param string      $localeCode
     *
     * @throws ExpectationException
     */
    protected function checkCellThumbnail(NodeElement $image, $code, $channelCode, $localeCode)
    {
        $thumbnailPath = $image->getAttribute('src');

        if (0 === preg_match(sprintf('`[_./]%s([_./]|$)`', $channelCode), $thumbnailPath)) {
            throw $this->createExpectationException(sprintf(
                'Expecting thumbnail path of row "%s" to contain scope "%s", full path is "%s".',
                $code,
                $channelCode,
                $thumbnailPath
            ));
        }

        if (null !== $localeCode && 0 === preg_match(sprintf('`[_./]%s([_./]|$)`', $localeCode), $thumbnailPath)) {
            throw $this->createExpectationException(sprintf(
                'Expecting thumbnail path of row "%s" to contain locale code "%s", full path is "%s".',
                $code,
                $localeCode,
                $thumbnailPath
            ));
        }
    }

    public function iClickOnTheActionOfTheRowWhichContains($actionName, $element)
    {
        if (in_array($actionName, ['Approve all', 'Remove', 'Reject all'])) {
            $this->spin(function () use ($element, $actionName) {
                $datagrid = $this->getDatagrid();
                $row = $datagrid->getRow($element);

                return $row->find('css', sprintf('.proposalActionButton:contains("%s")', $actionName));
            }, sprintf('Can not find proposal action %s for the row %s', $actionName, $element))->click();

            return;
        }

        parent::iClickOnTheActionOfTheRowWhichContains($actionName, $element);
    }
}
