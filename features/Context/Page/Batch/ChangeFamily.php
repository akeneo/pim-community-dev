<?php

namespace Context\Page\Batch;

use Context\Page\Base\Wizard;
use Behat\Mink\Element\Element;

/**
 * Batch ChangeFamily page
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChangeFamily extends Wizard
{
    /**
     * {@inheritdoc}
     */
    public function fillField($labelContent, $value, Element $element = null)
    {
        if ('Family' === $labelContent && 'None' === $value) {
            $value = '';
        }

        return parent::fillField($labelContent, $value, $element);
    }
}
