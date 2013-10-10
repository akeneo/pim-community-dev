<?php

namespace Context\Page\Locale;

use Context\Page\Base\Grid;

/**
 * Behat context page for locale index
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Index extends Grid
{
    /**
     * @var string $path
     */
    protected $path = '/configuration/locale/_grid_';

    /**
     * @param string $locale
     *
     * @return \Behat\Mink\Element\NodeElement
     */
    public function findActivatedLocale($locale)
    {
        return $this->getRow($locale)->find('css', 'span.label-success');
    }

    /**
     * @param string $locale
     *
     * @return \Behat\Mink\Element\NodeElement
     */
    public function findDeactivatedLocale($locale)
    {
        return $this->getRow($locale)->find('css', 'span.label-important');
    }
}
