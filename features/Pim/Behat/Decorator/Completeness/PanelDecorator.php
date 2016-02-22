<?php

namespace Pim\Behat\Decorator\Completeness;

use Behat\Mink\Element\NodeElement;
use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

/**
 * This class contains methods to find specific completeness blocks from the panel in the product edit form.
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PanelDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    /** @var array Selectors to ease find */
    protected $selectors = [
        'Completeness blocks' => [
            'css'        => '.completeness-block',
            'decorators' => [
                'Pim\Behat\Decorator\Completeness\BlockDecorator'
            ]
        ]
    ];

    /**
     * @param string $locale en_US, fr_FR, etc.
     *
     * @return NodeElement
     */
    public function findCompletenessForLocale($locale)
    {
        $block = $this->findBlock(
            sprintf($this->selectors['Completeness blocks']['css'] . ' header .locale[data-locale="%s"]', $locale)
        );
        $completeness = $block->getParent()->getParent();

        return $this->decorate($completeness, $this->selectors['Completeness blocks']['decorators']);
    }

    /**
     * @param int $position Begin to 1
     *
     * @throws \LogicException If the nth completeness is not found
     *
     * @return null|NodeElement
     */
    public function findNthCompleteness($position)
    {
        $blocks = $this->findAllBlocks($this->selectors['Completeness blocks']['css']);
        if (!is_array($blocks) || count($blocks) < $position) {
            throw new \LogicException(sprintf(
                'The completeness in position %s has not been found. It seems there is less then %s completenesses',
                $position,
                $position
            ));
        }

        return $this->decorate($blocks[$position - 1], $this->selectors['Completeness blocks']['decorators']);
    }

    /**
     * Spin to know if the panel is available
     *
     * @param string $selector
     *
     * @return NodeElement
     */
    protected function findBlock($selector)
    {
        return $this->spin(function () use ($selector) {
            return $this->find('css', $selector);
        }, 'Can\'t find completeness block in panel');
    }

    /**
     * @param string $selector
     *
     * @return []NodeElement
     */
    protected function findAllBlocks($selector)
    {
        return $this->spin(function () use ($selector) {
            return $this->findAll('css', $selector);
        }, 'Can\'t find completeness block in panel');
    }
}
