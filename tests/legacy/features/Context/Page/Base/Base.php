<?php

namespace Context\Page\Base;

use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Context\FeatureContext;
use Context\Spin\SpinCapableTrait;
use Context\Spin\TimeoutException;
use Context\Traits\ClosestTrait;
use Pim\Behat\Decorator\Common\DropdownMenuDecorator;
use Pim\Behat\Decorator\ElementDecorator;
use SensioLabs\Behat\PageObjectExtension\PageObject\Element;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

/**
 * Base page
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Base extends Page
{
    use SpinCapableTrait;
    use ClosestTrait;

    protected $elements = [
        'Body'                   => ['css' => 'body'],
        'Dialog'                 => ['css' => 'div.modal'],
        'Title'                  => ['css' => '.AknTitleContainer-title'],
        'Product title'          => ['css' => '.entity-title'],
        'HeadTitle'              => ['css' => 'title'],
        'Flash messages'         => ['css' => '.flash-messages-holder'],
        'Navigation Bar'         => ['css' => '.AknHeader-menu'],
        'Container'              => ['css' => '#container'],
        'Locales dropdown'       => ['css' => '#locale-switcher'],
        'Tabs'                   => ['css' => '#form-navbar'],
        'Oro tabs'               => ['css' => '.navbar.scrollspy-nav, .AknHorizontalNavtab'],
        'Form tabs'              => ['css' => '.nav-tabs.form-tabs'],
        'Active tab'             => ['css' => '.form-horizontal .tab-pane.active'],
        'Column navigation link' => ['css' => '.column-navigation-link'],
        'Current column link'    => ['css' => '.AknColumn-navigationLink--active'],
        'Secondary actions'      => ['css' => '.secondary-actions', 'decorators' => [DropdownMenuDecorator::class]],
    ];

    /**
     * {@inheritdoc}
     */
    public function getElement($name)
    {
        $element = $this->createElement($name);

        if (isset($this->elements[$name]['decorators'])) {
            $element = $this->decorate($element, $this->elements[$name]['decorators']);
        }

        return $element;
    }

    /**
     * Decorates an element
     *
     * @param NodeElement $element
     * @param array       $decorators
     *
     * @return ElementDecorator
     */
    protected function decorate(NodeElement $element, array $decorators)
    {
        foreach ($decorators as $decorator) {
            $element = new $decorator($element);
        }

        return $element;
    }

    /**
     * {@inheritdoc}
     */
    public function findField($locator)
    {
        return $this->spin(function () use ($locator) {
            return parent::findField($locator);
        }, sprintf('Can\'t find the field with given locator (%s)', $locator));
    }

    /**
     * {@inheritdoc}
     */
    public function fillField($locator, $value)
    {
        $field = $this->findField($locator);

        if (null === $field) {
            throw new ElementNotFoundException($this->getSession(), 'form field', 'id|name|label|value', $locator);
        }

        $class = $field->getAttribute('class');
        if (strpos($class, 'wysiwyg') !== false || strpos($class, 'datepicker') !== false) {
            $this->getSession()->executeScript(
                sprintf(
                    "$('#%s').val('%s').trigger('change');",
                    $field->getAttribute('id'),
                    $value
                )
            );
        } else {
            $field->setValue($value);
        }

        try {
            $this->getSession()->executeScript(
                "$('.select2-drop-active input:visible').trigger($.Event('keydown', {which: 9, keyCode: 9}));"
            );
        } catch (UnsupportedDriverActionException $e) {
        }
    }

    /**
     * @param string $locator
     * @param string $value
     */
    public function simpleFillField($locator, $value)
    {
        $this->spin(function () use ($locator) {
            return parent::findField($locator);
        }, sprintf('Cannot find field "%s"', $locator))->setValue($value);
    }

    /**
     * Toggle the bootstrapSwitch on or off
     *
     * @param string $locator
     * @param bool   $on
     */
    public function toggleSwitch($locator, $on = true)
    {
        $field = $this->findField($locator);

        $this->spin(function () use ($field, $on) {
            if ($on !== $field->isChecked()) {
                $switch = $this->getClosest($field, 'switch');
                if (null === $switch) {
                    return false;
                }
                $switch->click();
            }

            return $on === $field->isChecked();
        }, sprintf('Switch label "%s" not found.', $locator));
    }

    /**
     * @return string
     */
    public function getHeadTitle()
    {
        return $this->getElement('HeadTitle')->getHtml();
    }

    /**
     * @param array $options
     *
     * @return string
     */
    public function getUrl(array $options = [])
    {
        $url = $this->getPath();

        foreach ($options as $parameter => $value) {
            $url = str_replace(sprintf('{%s}', $parameter), $value, $url);
        }

        $baseUrl = rtrim($this->getParameter('base_url'), '/').'/';

        return 0 !== strpos($url, 'http') ? $baseUrl.ltrim($url, '/') : $url;
    }

    /**
     * Get page title
     *
     * @throws \Exception
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->spin(function () {
            $title = $this->find('css', $this->elements['Title']['css']);

            if (null === $title) {
                $title = $this->find('css', $this->elements['Product title']['css']);
            }

            return $title;
        }, 'Could not find the page title')->getText();
    }

    /**
     * Overriden for compatibility with links
     *
     * @param string  $locator
     * @param boolean $forceVisible
     *
     * @throws ElementNotFoundException
     */
    public function pressButton($locator, $forceVisible = false)
    {
        $this->spin(function () use ($locator, $forceVisible) {
            $result = $forceVisible ? $this->getVisibleButton($locator) : $this->getButton($locator);

            if (null === $result) {
                $result = $this->find(
                    'named',
                    [
                        'link',
                        $this->getSession()->getSelectorsHandler()->xpathLiteral($locator)
                    ]
                );
            }
            if (null !== $result) {
                $result->click();

                return true;
            }
        }, sprintf('Can not find any "%s" button', $locator));
    }

    /**
     * Get button
     *
     * @param string  $locator
     *
     * @return NodeElement|null
     */
    public function getButton($locator)
    {
        // Search with exact name at first
        $button = $this->find('xpath', sprintf("//button[normalize-space(text()) = '%s']", $locator));
        if (null === $button) {
            $button = $this->find('xpath', sprintf("//a[normalize-space(text()) = '%s']", $locator));
        }
        if (null === $button) {
            $button = $this->find('css', sprintf('a[title="%s"]', $locator));
        }
        if (null === $button) {
            $button = $this->find('css', sprintf('div.AknButton[title="%s"]', $locator));
        }
        if (null === $button) {
            $button = $this->find(
                'xpath',
                sprintf("//div[contains(@class,'AknButton')][normalize-space(text()) = '%s']", $locator)
            );
        }
        if (null === $button) {
            // Use Mink search, which use "contains" xpath condition
            $button = $this->findButton($locator);
        }
        return $button;
    }

    /**
     * Get icon button
     *
     * @param string  $locator
     *
     * @return NodeElement|null
     */
    public function getIconButton($locator)
    {
        $button = null;
        $icon = $this->find('css', sprintf('i[data-original-title="%s"]', $locator));

        if (null !== $icon) {
            $button = $this->getClosest($icon, 'AknIconButton');
        }

        return $button;
    }

    /**
     * Get visible button
     *
     * @param string $locator
     *
     * @return NodeElement|null
     */
    public function getVisibleButton($locator)
    {
        $button = $this->getFirstVisible(
            $this->findAll('xpath', sprintf("//button[normalize-space(text()) = '%s']", $locator))
        );
        if (null === $button) {
            $button = $this->getFirstVisible(
                $this->findAll('xpath', sprintf("//a[normalize-space(text()) = '%s']", $locator))
            );
        }
        if (null === $button) {
            $button = $this->getFirstVisible(
                $this->findAll(
                    'xpath',
                    sprintf("//*[contains(@class, 'AknButton')][normalize-space(text()) = '%s']", $locator)
                )
            );
        }
        if (null === $button) {
            $button =  $this->getFirstVisible(
                $this->findAll('css', sprintf('a[title="%s"]', $locator))
            );
        }
        if (null === $button) {
            $button = $this->getFirstVisible(
                $this->findAll('named', ['button', $locator])
            );
        }

        return $button;
    }

    /**
     * Confirm the dialog action
     */
    public function confirmDialog()
    {
        $this->spin(function () {
            $loading = $this->find('css', '.loading-mask');

            return null === $loading || !$loading->isVisible();
        }, 'Loading mask is still visible');

        $button = $this->spin(function () {
            return $this->getConfirmDialog()->find('css', '.ok');
        }, 'Could not find the confirmation button');

        $button->click();
    }

    /**
     * Get the confirm dialog element
     *
     * @return NodeElement
     */
    protected function getConfirmDialog()
    {
        return $this->spin(function () {
            return $this->getElement('Dialog');
        }, 'Could not find dialog popin');
    }

    /**
     * Get the confirm dialog title
     *
     * @return string
     */
    public function getConfirmDialogTitle()
    {
        $element = $this->getConfirmDialog();

        return $element
            ->find('css', 'div.modal-header')
            ->getText();
    }

    /**
     * Get confirm dialog content
     *
     * @return string
     */
    public function getConfirmDialogContent()
    {
        $element = $this->getConfirmDialog();

        return $element
            ->find('css', 'div.modal-body')
            ->getText();
    }

    /**
     * Cancel the dialog action
     */
    public function cancelDialog()
    {
        $this->spin(function () {
            $element = $this->getElement('Dialog');
            if (null === $element) {
                return null;
            }

            return $element->find('css', '.cancel');
        }, 'Could not find the cancel button')->click();
    }

    /**
     * Find a validation tooltip containing a text
     *
     * @param string $text
     *
     * @return null|Element
     */
    public function findValidationTooltip(string $text)
    {
        return $this->find('css', sprintf('.validation-tooltip[data-original-title="%s"]', $text));
    }

    /**
     * Click on the akeneo logo
     */
    public function clickOnAkeneoLogo()
    {
        $this->spin(function () {
            return $this->getElement('Navigation Bar')->find('css', '.AknHeader-logoImage');
        }, 'Cannot find Akeneo logo')->click();
    }

    /**
     * Gets a dropdown button containing text
     *
     * @param string $text
     *
     * @return null|Element
     */
    public function getDropdownButton($text)
    {
        return $this->spin(function () use ($text) {
            $toggle = $this->find('css', sprintf('*[data-toggle="dropdown"]:contains("%s")', $text));
            if (null !== $toggle) {
                if (!$toggle->getParent()->hasClass('open')) {
                    $toggle->click();
                };

                return $toggle;
            }
        }, sprintf('Dropdown button "%s" not found', $text));
    }

    /**
     * @param string $item
     * @param string $button
     *
     * @return NodeElement
     */
    public function getDropdownButtonItem($item, $button)
    {
        $dropdownToggle = $this->getDropdownButton($button);
        $dropdownMenu = $dropdownToggle->getParent()->find('css', '.dropdown-menu, .AknDropdown-menu');

        return $this->spin(function () use ($dropdownMenu, $item) {
            return $dropdownMenu->find('css', sprintf('.AknDropdown-menuLink:contains("%s")', $item));
        }, sprintf('Item "%s" of dropdown button "%s" not found', $item, $button));
    }

    /**
     * @return int timeout in millisecond
     */
    protected function getTimeout()
    {
        return FeatureContext::getTimeout();
    }

    /**
     * Drags an element on another one.
     * Works better than the standard dragTo.
     *
     * @param $element
     * @param $dropZone
     */
    public function dragElementTo($element, $dropZone)
    {
        $session = $this->getSession()->getDriver()->getWebDriverSession();

        $from = $session->element('xpath', $element->getXpath());
        $to = $session->element('xpath', $dropZone->getXpath());

        $session->moveto(['element' => $from->getID()]);
        $session->buttondown('');
        $session->moveto(['element' => $to->getID()]);
        $session->buttonup('');
    }

    /**
     * Visit the specified tab
     *
     * @param string $tab
     */
    public function visitTab($tab)
    {
        $tabs = $this->getPageTabs();

        $tabDom = $this->spin(function () use ($tabs, $tab) {
            $link = $tabs->findLink($tab);
            if (null !== $link) {
                return $link;
            }

            $matchingElements = array_filter(
                $tabs->findAll('css', '.AknHorizontalNavtab-link'),
                function (NodeElement $element) use ($tab) {
                    return strpos($element->getText(), $tab) !== false;
                }
            );

            return array_shift($matchingElements);
        }, sprintf('Could not find a tab named "%s"', $tab));

        $this->spin(function () {
            $loading = $this->find('css', '.loading-mask');

            return null === $loading || !$loading->isVisible();
        }, sprintf('Could not visit tab %s because of loading wrapper', $tab));

        $this->spin(function () use ($tabDom) {
            $tabDom->click();

            return 0 < count(array_filter(
                ['active', 'tab-scrollable', 'AknHorizontalNavtab-item--active'],
                function ($class) use ($tabDom) {
                    return $tabDom->getParent()->hasClass($class);
                }
            ));
        }, sprintf('Cannot switch to the tab %s', $tab));
    }

    /**
     * @param string $tabName
     *
     * @throws TimeoutException
     */
    public function visitColumnTab($tabName)
    {
        $this->spin(function () use ($tabName) {
            foreach ($this->getColumnTabs() as $tab) {
                if (trim($tab->getText()) === $tabName) {
                    $tab->click();

                    return true;
                }
            }

            return null;
        }, sprintf('Can not find any column tab named "%s"', $tabName));
    }

    /**
     * @return NodeElement|null
     */
    public function getCurrentColumnTab()
    {
        return $this->find('css', $this->elements['Current column link']['css']);
    }

    /**
     * @return NodeElement[]
     */
    public function getColumnTabs()
    {
        return $this->findAll('css', $this->elements['Column navigation link']['css']);
    }

    /**
     * Get the form tab containg $tab text
     *
     * @param string $tab
     *
     * @return NodeElement|null
     */
    public function getFormTab($tab)
    {
        $tabs = $this->getPageTabs();

        try {
            $node = $this->spin(function () use ($tabs, $tab) {
                return $tabs->find('css', sprintf('a:contains("%s")', $tab));
            }, sprintf('Cannot find form tab "%s"', $tab));
        } catch (\Exception $e) {
            $node = null;
        }

        return $node;
    }

    /**
     * Returns the tabs of the current page, if any.
     *
     * @return NodeElement
     */
    protected function getPageTabs()
    {
        return $this->spin(function () {
            $tabs = $this->find('css', $this->elements['Tabs']['css']);
            if (null === $tabs) {
                $tabs = $this->find('css', $this->elements['Oro tabs']['css']);
            }
            if (null === $tabs) {
                $tabs = $this->find('css', $this->elements['Form tabs']['css']);
            }

            return $tabs;
        }, 'Cannot find any tabs in this page');
    }

    /**
     * Returns the first visible element
     *
     * @param $nodeElements NodeElement[]
     *
     * @return NodeElement|null
     */
    protected function getFirstVisible(array $nodeElements)
    {
        foreach ($nodeElements as $nodeElement) {
            if ($nodeElement->isVisible()) {
                return $nodeElement;
            }
        }

        return null;
    }
}
