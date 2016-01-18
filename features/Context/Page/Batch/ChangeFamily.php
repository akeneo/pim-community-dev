<?php

namespace Context\Page\Batch;

use Behat\Mink\Element\Element;
use Context\Page\Base\Wizard;

/**
 * Batch ChangeFamily page
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChangeFamily extends Wizard
{
    /**
     * {@inheritdoc}
     */
    public function fillField($locator, $value, Element $modal = null)
    {
        // Simply do not select a family
        if ('Family' === $locator && 'None' === $value) {
            return;
        }

        $select2Locator = sprintf(
            'label:contains("%s") + .select2-container',
            $locator
        );

        $selectContainer = $this->spin(function () use ($select2Locator) {
            return $this->find('css', $select2Locator);
        });

        if ($selectContainer) {
            $this->fillSelect2Field($selectContainer, $value);
        } else {
            parent::fillField($locator, $value, $modal);
        }
    }
}
