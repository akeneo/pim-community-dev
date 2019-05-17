<?php

namespace Context\Page\Element;

use Behat\Mink\Element\NodeElement;
use Context\Spin\SpinCapableTrait;
use Context\Traits\ClosestTrait;
use SensioLabs\Behat\PageObjectExtension\PageObject\Element;

/**
 * Completeness Widget element
 *
 * TODO: Drop this and use the same decorator as the PEF
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessWidget extends Element
{
    use ClosestTrait;

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
        return $this->getChannelNode($channel)->find('css', '.stats')->getText();
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
        $localeLines = $this->getChannelNode($channel)->findAll('css', '.content > *');
        foreach ($localeLines as $localeLine) {
            $localeCell = $localeLine->find('css', '.locale');

            if (null !== $localeCell && $localeCell->getText() === $locale) {
                return $localeLine->find('css', '.literal-progress')->getText();
            }
        }

        throw new \InvalidArgumentException(
            sprintf('Could not find locale "%s" for channel "%s"', $locale, $channel)
        );
    }

    /**
     * @param $channel
     *
     * @return NodeElement
     */
    protected function getChannelNode($channel)
    {
        return $this->spin(function () use ($channel) {
            $cell = $this->find('css', sprintf('.channel:contains("%s")', $channel));
            if (null !== $cell) {
                return $this->getClosest($cell, 'completeness-block');
            }

            return null;
        }, sprintf('Could not find completeness widget for channel "%s"', $channel));
    }
}
