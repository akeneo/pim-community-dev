<?php

namespace Context\Page\Element;

use Context\Spin\SpinCapableTrait;
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
    use SpinCapableTrait;

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
        $cell = $this->spin(function () use ($channel) {
            return $this->find('css', sprintf('.header:contains("%s") .pull-right', $channel));
        }, sprintf('Could not find channel "%s"', $channel));

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
        $localeLines = $this->findAll('css', sprintf('.channel:contains("%s") tr', $channel));
        foreach ($localeLines as $localeLine) {
            $localeCell = $localeLine->find('css', 'td.locale');
            if (null !== $localeCell) {
                if ($localeCell->getText() === $locale) {
                    return $localeLine->find('css', 'td.total')->getText();
                }
            }
        }

        throw new \InvalidArgumentException(
            sprintf('Could not find locale "%s" for channel "%s"', $locale, $channel)
        );
    }
}
