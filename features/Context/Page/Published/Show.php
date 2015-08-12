<?php

namespace Context\Page\Published;

use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ElementNotFoundException;
use Context\Page\Base\Form;

/**
 * Show product page
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class Show extends Form
{
    /**
     * @var string
     */
    protected $path = '/workflow/published-product/{id}/view';

    /**
     * @param string $name
     *
     * @return NodeElement
     */
    public function findField($name)
    {
        $currency = null;
        if (1 === preg_match('/in ((?:.){1,3})$/', $name)) {
            // Price in EUR
            list($name, $currency) = explode(' in ', $name);

            return $this->findPriceField($name, $currency);
        } elseif (1 < str_word_count($name)) {
            // mobile Description
            $words = explode(' ', $name);
            $name = implode(' ', $words);
        }
        $label = $this->find('css', sprintf('label:contains("%s")', $name));

        if (!$label) {
            throw new ElementNotFoundException($this->getSession(), 'label ', 'value', $name);
        }

        $field = $label->getParent()->find('css', 'div.value-field');

        if (!$field) {
            throw new ElementNotFoundException($this->getSession(), 'field ', 'value-field', $name);
        }

        return $field;
    }
}
