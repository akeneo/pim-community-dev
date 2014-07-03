<?php

namespace Context\Page\Base;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Behat\Mink\Exception\ElementNotFoundException;

/**
 * Base page
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Base extends Page
{
    protected $elements = array(
        'Dialog'         => array('css' => 'div.modal'),
        'Title'          => array('css' => '.navbar-title'),
        'HeadTitle'      => array('css' => 'title'),
        'Flash messages' => array('css' => '.flash-messages-holder'),
        'Navigation Bar' => array('css' => 'header#oroplatform-header'),
        'Container'      => array('css' => '#container'),
    );

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
     * Toggle the bootstrapSwitch on or off
     *
     * @param string  $locator
     * @param boolean $on
     */
    public function toggleSwitch($locator, $on = true)
    {
        $field = $this->findField($locator);
        if ($field->isChecked() != $on) {
            $field->getParent()->find('css', 'label')->click();
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
    public function getUrl(array $options = array())
    {
        $url = $this->getPath();

        foreach ($options as $parameter => $value) {
            $url = str_replace(sprintf('{%s}', $parameter), $value, $url);
        }

        return $url;
    }

    /**
     * Get page title
     * @return string
     */
    public function getTitle()
    {
        $elt = $this->getElement('Title');

        $subtitle  = $elt->find('css', '.sub-title');
        $separator = $elt->find('css', '.separator');
        $name      = $elt->find('css', '.product-name');

        if (!$subtitle || !$separator || !$name) {
            throw new \Exception('Could not find the page title');
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

        if (!$button) {
            $button =  $this->find(
                'named',
                array(
                    'link',
                    $this->getSession()->getSelectorsHandler()->xpathLiteral($locator)
                )
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
     * @return NodeElement
     */
    public function getButton($locator)
    {
        // Search with exact name at first
        $button = $this->find('xpath', sprintf("//button[text() = '%s']", $locator));

        if (!$button) {
            $button = $this->find('xpath', sprintf("//a[text() = '%s']", $locator));
        }

        if (!$button) {
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
        $element = $this->getConfirmDialog();

        $button = $element->find('css', 'a.btn.ok');

        if (!$button) {
            throw new \Exception('Could not find the confirmation button');
        }

        $button->click();
    }

    /**
     * Get the confirm dialog element
     * @throws \Exception
     * @return \SensioLabs\Behat\PageObjectExtension\PageObject\Element
     */
    protected function getConfirmDialog()
    {
        $element = $this->getElement('Dialog');

        if (!$element) {
            throw new \Exception('Could not find dialog window');
        }

        return $element;
    }

    /**
     * Get the confirm dialog title
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

        if (!$element) {
            throw new \Exception('Could not find dialog window');
        }

        // TODO: Use the 'Cancel' button instead of the 'Close' button
        // (waiting for BAP to get the 'Cancel' button on grid actions)
        $button = $element->find('css', 'a.close');

        if (!$button) {
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
     * Find a flash message containing text
     *
     * @param string $text
     *
     * @throws \Exception
     * @return null|Element
     */
    public function findFlashMessage($text)
    {
        $holder = $this->getElement('Flash messages');

        if (!$holder) {
            throw new \Exception('Could not find the flash messages holder');
        }

        return $holder->find('css', sprintf('div.message:contains("%s")', $text));
    }

    /**
     * @param string $item
     * @param string $button
     *
     * @throws \InvalidArgumentException
     *
     * @return NodeElement
     */
    public function getDropdownButtonItem($item, $button)
    {
        $dropdown = $this
            ->find('css', sprintf('div.btn-group:contains("%s")', $button));

        if (!$dropdown || !$dropdown->find('css', 'button.dropdown-toggle')) {
            throw new \InvalidArgumentException(sprintf('Dropdown button "%s" not found', $button));
        }
        $dropdown->find('css', 'button.dropdown-toggle')->click();

        $listItem = $dropdown->find('css', sprintf('li:contains("%s") a', $item));
        if (!$listItem) {
            throw new \InvalidArgumentException(sprintf('Item "%s" of dropdown button "%s" not found', $item, $button));
        }

        return $listItem;
    }
}
