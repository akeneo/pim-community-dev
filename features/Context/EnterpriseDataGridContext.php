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
     * | product  | author | attribute  | original | new         |
     * | my-hoody | Mary   | Lace color |          | Black,White |
     *
     * Note: As values are not ordered you can add multiple values using semicolon separator.
     * Warning: we split the results with space separator so values with spaces will fail.
     *
     * @Given /^I should see the following proposals?:$/
     *
     * @param TableNode $table
     */
    public function iShouldSeeTheFollowingProposals(TableNode $table)
    {
        foreach ($table->getHash() as $hash) {
            $datagrid = $this->datagrid->getGrid();

            $change = $this->spin(function () use ($datagrid, $hash) {
                return $datagrid->find('css', sprintf(
                    'table.proposal-changes[data-product="%s"][data-attribute="%s"][data-author="%s"]',
                    $hash['product'],
                    $hash['attribute'],
                    $hash['author']
                ));
            }, sprintf('Unable to find the change on the proposal for attribute "%s"', $hash['attribute']));

            $original = $change->find('css', '.original-value');
            $new      = $change->find('css', '.new-value');

            $originalExpectedValues = explode(',', $hash['original']);
            $newExpectedValues      = explode(',', $hash['new']);
            $rawOriginalValues      = explode(' ', $original->getText());
            $rawNewValues           = explode(' ', $new->getText());

            sort($originalExpectedValues);
            sort($newExpectedValues);
            sort($rawOriginalValues);
            sort($rawNewValues);

            $originalExpected = implode(', ', $originalExpectedValues);
            $newExpected      = implode(', ', $newExpectedValues);
            $originalValues   = implode(', ', $rawOriginalValues);
            $newValues        = implode(', ', $rawNewValues);

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
                        $newValues,
                        $newExpected
                    )
                );
            }
        }
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
