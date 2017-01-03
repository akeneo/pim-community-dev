<?php

namespace Context\Page\Base;

use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Context\FeatureContext;
use Context\Spin\SpinCapableTrait;
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

    protected $elements = [
        'Body'             => ['css' => 'body'],
        'Dialog'           => ['css' => 'div.modal'],
        'Title'            => ['css' => '.navbar-title'],
        'Product title'    => ['css' => '.entity-title'],
        'HeadTitle'        => ['css' => 'title'],
        'Flash messages'   => ['css' => '.flash-messages-holder'],
        'Navigation Bar'   => ['css' => 'header#oroplatform-header'],
        'Container'        => ['css' => '#container'],
        'Locales dropdown' => ['css' => '#locale-switcher'],
        'Tabs'             => ['css' => '#form-navbar'],
        'Oro tabs'         => ['css' => '.navbar.scrollspy-nav'],
        'Form tabs'        => ['css' => '.nav-tabs.form-tabs'],
        'Active tab'       => ['css' => '.form-horizontal .tab-pane.active'],
    ];

    /**
     * {@inheritdoc}
     */
    public function getElement($name)
    {
        $element = parent::getElement($name);

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
        if ($field->isChecked() != $on) {
            $switch = $this->spin(function () use ($field) {
                return $field->getParent()->find('css', 'label');
            }, sprintf('Switch label "%s" not found.', $locator));
            $switch->click();
        }
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

        return $url;
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
        $elt = $this->getElement('Title');

        $subtitle  = $elt->find('css', '.sub-title');
        $separator = $elt->find('css', '.separator');
        $name      = $elt->find('css', '.product-name');

        if (null === $subtitle || null === $separator || null === $name) {
            $titleElt = $this->spin(function () {
                return $this->getElement('Product title')->find('css', '.object-label');
            }, "Could not find the page title");

            return $titleElt->getText();
        }

        return sprintf(
            '%s%s%s',
            trim($subtitle->getText()),
            trim($separator->getText()),
            trim($name->getText())
        );
    }

    /**
     * Overriden for compatibility with links
     *
     * @param string $locator
     *
     * @throws ElementNotFoundException
     */
    public function pressButton($locator)
    {
        $button = $this->getButton($locator);

        if (null === $button) {
            $button = $this->find(
                'named',
                [
                    'link',
                    $this->getSession()->getSelectorsHandler()->xpathLiteral($locator)
                ]
            );
        }

        if (null === $button) {
            throw new ElementNotFoundException($this->getSession(), 'button', 'id|name|title|alt|value', $locator);
        }

        $button->click();
    }

    /**
     * Get button
     *
     * @param string $locator
     *
     * @return NodeElement|null
     */
    public function getButton($locator)
    {
        // Search with exact name at first
        $button = $this->find('xpath', sprintf("//button[text() = '%s']", $locator));

        if (null === $button) {
            $button = $this->find('xpath', sprintf("//a[text() = '%s']", $locator));
        }

        if (null === $button) {
            $button = $this->find('css', sprintf('a[title="%s"]', $locator));
        }

        if (null === $button) {
            // Use Mink search, which use "contains" xpath condition
            $button = $this->findButton($locator);
        }

        return $button;
    }

    /**
     * Confirm the dialog action
     */
    public function confirmDialog()
    {
        $button = $this->spin(function () {
            return $this->getConfirmDialog()->find('css', '.ok');
        }, 'Could not find the confirmation button');

        $button->click();
    }

    /**
     * Get the confirm dialog element
     *
     * @throws \Exception
     *
     * @return \SensioLabs\Behat\PageObjectExtension\PageObject\Element
     */
    protected function getConfirmDialog()
    {
        $element = $this->getElement('Dialog');

        if (null === $element) {
            throw new \Exception('Could not find dialog window');
        }

        return $element;
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
        $element = $this->getElement('Dialog');

        if (null === $element) {
            throw new \Exception('Could not find dialog window');
        }

        // TODO: Use the 'Cancel' button instead of the 'Close' button
        // (waiting for BAP to get the 'Cancel' button on grid actions)
        $button = $element->find('css', 'a.close');

        if (null === $button) {
            throw new \Exception('Could not find the cancel button');
        }

        $button->click();
    }

    /**
     * Find a validation tooltip containing a text
     *
     * @param string $text
     *
     * @return null|Element
     */
    public function findValidationTooltip($text)
    {
        return $this->find('css', sprintf('.validation-tooltip[data-original-title="%s"]', $text));
    }

    /**
     * Click on the akeneo logo
     */
    public function clickOnAkeneoLogo()
    {
        $this
            ->getElement('Navigation Bar')
            ->find('css', 'h1.logo a')
            ->click();
    }

    /**
     * @param string $item
     * @param string $button
     *
     * @return NodeElement
     */
    public function getDropdownButtonItem($item, $button)
    {
        $dropdownToggle = $this->spin(function () use ($button) {
            return $this->find('css', sprintf('.dropdown-toggle:contains("%s")', $button));
        }, sprintf('Dropdown button "%s" not found', $button));

        $dropdownToggle->click();

        $dropdownMenu = $dropdownToggle->getParent()->find('css', '.dropdown-menu');

        return $this->spin(function () use ($dropdownMenu, $item) {
            return $dropdownMenu->find('css', sprintf('li:contains("%s") a', $item));
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
            return $tabs->findLink($tab);
        }, sprintf('Could not find a tab named "%s"', $tab));

        $this->spin(function () {
            $loading = $this->find('css', '#loading-wrapper');

            return null === $loading || !$loading->isVisible();
        }, sprintf('Could not visit tab %s because of loading wrapper', $tab));

        $this->spin(function () use ($tabDom) {
            $tabDom->click();

            return $tabDom->getParent()->hasClass('active') || $tabDom->getParent()->hasClass('tab-scrollable');
        }, sprintf('Cannot switch to the tab %s', $tab));
    }

    /**
     * Get the tabs in the current page
     *
     * @return NodeElement[]
     */
    public function getTabs()
    {
        $tabs = $this->spin(function () {
            return $this->find('css', $this->elements['Tabs']['css']);
        }, sprintf('Cannot find "%s" element', $this->elements['Tabs']['css']));

        if (null === $tabs) {
            $tabs = $this->getElement('Oro tabs');
        }

        return $tabs->findAll('css', 'a');
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
     * Get the specified tab
     *
     * @return NodeElement
     */
    public function getTab($tab)
    {
        return $this->find('css', sprintf('a:contains("%s")', $tab));
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
}
