<?php

namespace Pim\Behat\Decorator\Completeness;

use Behat\Mink\Element\NodeElement;
use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

/**
 * This class contains methods to find some properties on completeness blocks from the panel in the product edit form.
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BlockDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    /** @var array Selectors to ease find */
    protected $selectors = [
        'locale' => [
            'css'       => 'header .locale',
            'attribute' => 'data-locale'
        ],
        'state' => [
            'attribute' => 'data-closed'
        ]
    ];

    /**
     * @param NodeElement $completeness
     *
     * @return string
     */
    public function getLocale()
    {
        $locale = $this->spin(function () {
            return $this->find('css', $this->selectors['locale']['css']);
        }, 'Can\'t find locale in completeness block');

        return $locale->getAttribute($this->selectors['locale']['attribute']);
    }

    /**
     * @param NodeElement $completeness
     *
     * @return string
     */
    public function getState()
    {
        return $this->spin(function () {
            return $this->getAttribute($this->selectors['state']['attribute']);
        }, 'Can\'t find state open or close in completeness block');
    }
}
