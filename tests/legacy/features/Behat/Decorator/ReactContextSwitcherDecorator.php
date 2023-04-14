<?php

declare(strict_types=1);

namespace Pim\Behat\Decorator;

use Behat\Mink\Element\Element;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReactContextSwitcherDecorator extends ContextSwitcherDecorator
{
    private array $localesMappingEn_US = [
        'en_US' => 'English (United States)',
        'fr_FR' => 'French (France)',
        'de_DE' => 'German (Germany)',
        'en_GB' => 'English (United Kingdom)',
    ];

    private array $localesMappingFr_FR = [
        'en_US' => 'anglais (États-Unis)',
        'fr_FR' => 'français (France)',
    ];

    public function switchLocale(string $localeCode): void
    {
        $localeButton = $this->getLocaleButton();
        $localesMapping = \str_contains($localeButton->getText(), 'français') ? $this->localesMappingFr_FR : $this->localesMappingEn_US;

        $localeButton->click();
        $expectedText = $localesMapping[$localeCode];

        $localeOption = $this->spin(function () use ($expectedText) {
            $itemsFromRoot = $this->getBody()->findAll('css', '#dropdown-root *[role=listbox] > *');

            foreach ($itemsFromRoot as $listItem) {
                $text = $listItem->getHtml();
                if (str_contains($text, $expectedText) && $listItem->isVisible()) {
                    return $listItem;
                }
            }

            return false;
        }, \sprintf('Couldn\'t find Locale Item for local %s', $localeCode));
        $localeOption->click();
    }

    private function getLocaleButton()
    {
        return $this->spin(function () {
            $buttons = $this->findAll('css', 'button');

            /** @var Element $button */
            foreach ($buttons as $button) {
                $text = $button->getText();
                if (\str_starts_with($text, 'Locale:')) {
                    return $button;
                }
            }

            return null;
        }, 'Cannot find any Locale Button');
    }

    /**
     * @return string
     */
    public function getSelectedLocale()
    {
        $localeButton = $this->getLocaleButton();
        $localeButtonText = $localeButton->getText();

        foreach ($this->localesMappingEn_US as $key => $value) {
            if (\str_contains($localeButtonText, $value)) {
                return $key;
            }
        }

        return '';
    }
}
