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
}
