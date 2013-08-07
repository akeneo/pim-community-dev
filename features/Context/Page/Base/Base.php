<?php

namespace Context\Page\Base;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Behat\Mink\Exception\UnsupportedDriverActionException;

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
        'Dialog' => array('css' => 'div.modal'),
        'Title'  => array('css' => '.navbar-title'),
    );

    /**
     * {@inheritdoc}
     */
    public function fillField($locator, $value)
    {
        parent::fillField($locator, $value);

        try {
            $this->getSession()->executeScript(
                "$('.select2-drop-active input:visible').trigger($.Event('keydown', {which: 9, keyCode: 9}));"
            );
        } catch (UnsupportedDriverActionException $e) {
        }
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
     * Confirm the dialog action
     */
    public function confirmDialog()
    {
        $element = $this->getElement('Dialog');

        if (!$element) {
            throw new \Exception('Could not find dialog window');
        }

        $button = $element->find('css', 'a.btn.ok');

        if (!$button) {
            throw new \Exception('Could not find the confirmation button');
        }

        $button->click();
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
}
