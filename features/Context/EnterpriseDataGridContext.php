<?php

namespace Context;

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
     * @Then /^the row "([^"]+)" should contain the thumbnail for channel "([^"]+)"(?: and locale "([^"]+)")?$/
     *
     * @param string      $code
     * @param string      $channelCode
     * @param string|null $localeCode
     *
     * @throws ExpectationException
     */
    public function theRowShouldContainThumbnailForContext($code, $channelCode, $localeCode = null)
    {
        $cell  = $this->datagrid->getColumnNode('thumbnail', $code);
        $image = $cell->find('css', 'img');

        if (!$image) {
            throw $this->createExpectationException(
                sprintf('Column "thumbnail" of row "%s" contains no image.', $code)
            );
        }

        $thumbnailPath = $image->getAttribute('src');
        if (false === strpos($thumbnailPath, sprintf('_%s.', $channelCode))) {
            throw $this->createExpectationException(sprintf(
                'Expecting thumbnail path of row "%s" to contain scope "%s", got "%s".',
                $code,
                $channelCode,
                $thumbnailPath
            ));
        }

        if (null !== $localeCode && false === strpos($thumbnailPath, sprintf('_%s_', $localeCode))) {
            throw $this->createExpectationException(sprintf(
                'Expecting thumbnail path of row "%s" to contain locale code "%s", got "%s".',
                $code,
                $localeCode,
                $thumbnailPath
            ));
        }
    }
}
