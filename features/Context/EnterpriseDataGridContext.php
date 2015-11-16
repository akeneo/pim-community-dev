<?php

namespace Context;

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
