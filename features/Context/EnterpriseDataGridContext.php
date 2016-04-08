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
    public function __construct()
    {
        parent::__construct();

        $this->gridNames = array_merge(
            $this->gridNames,
            [
                'published products' => 'published-product-grid',
                'rules'              => 'rule-grid',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function iShouldSeeTheColumns($columns)
    {
        try {
            parent::iShouldSeeTheColumns($columns);
        } catch (ExpectationException $e) {
            // If the exception is thrown because of a wrong number of columns, we just ignore it
            // because some datagrids can have a different number of columns in the enterprise edition
            if (preg_match('/^Expected ([0-9])+ columns/', $e->getMessage())) {
                return;
            }

            throw $e;
        }
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
     * @param string $elements
     *
     * @throws ExpectationException
     *
     * @Then /^I should see assets? (.+)$/
     */
    public function iShouldSeeAsset($elements)
    {
        $this->iChangePageSize(100);
        parent::iShouldSeeEntities($elements);
    }

    /**
     * @param string $code
     *
     * @Given /^I filter by "asset category" with value "([^"]*)"$/
     */
    public function iFilterByAssetCategory($code)
    {
        $this->wait();
        if (strtolower($code) === 'unclassified') {
            $this->getCurrentPage()->clickUnclassifiedCategoryFilterLink();
        } else {
            $category = $this->getFixturesContext()->getAssetCategory($code);
            $this->getCurrentPage()->clickCategoryFilterLink($category);
        }

        $this->wait();
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
        $image = $this->datagrid->getCellImage($column, $code);
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
        $image = $this->datagrid->getCellImage('thumbnail', $code);
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
        foreach ($table->getHash() as $hash) {
            $datagrid = $this->datagrid->getGrid();

            $change = $this->spin(function () use ($datagrid, $hash) {
                $selector = 'table.proposal-changes[data-product="%s"][data-attribute="%s"][data-author="%s"]';
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

                return $datagrid->find('css', vsprintf($selector, $params));
            }, sprintf('Unable to find the change on the proposal for attribute "%s"', $hash['attribute']));

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
        }
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
}
