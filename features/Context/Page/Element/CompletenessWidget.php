<?php

namespace Context\Page\Element;

use SensioLabs\Behat\PageObjectExtension\PageObject\Element;

/**
 * Completeness Widget element
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessWidget extends Element
{
    /** @var array */
    protected $selector = array('css' => '#completeness-widget');

    /**
     * Get the channel completeness ratio inside the completeness widget
     *
     * @param string $channel
     *
     * @return string
     */
    public function getChannelCompleteness($channel)
    {
        $cell = $this->find('css', sprintf('tr:contains("%s") td:nth-child(3)', $channel));
        if (!$cell) {
            throw new \InvalidArgumentException(sprintf('Could not find channel "%s"', $channel));
        }

        return $cell->getText();
    }

    /**
     * Get the localized channel completeness ratio inside the completeness widget
     *
     * @param string $channel
     * @param string $locale
     *
     * @return string
     */
    public function getLocalizedChannelCompleteness($channel, $locale)
    {
        $cell = $this->find('css', sprintf('tr:contains("%s")[data-channel="%s"] td:nth-child(3)', $locale, $channel));
        if (!$cell) {
            throw new \InvalidArgumentException(sprintf('Could not find locale "%s" for channel "%s"', $locale, $channel));
        }

        return $cell->getText();
    }
}
