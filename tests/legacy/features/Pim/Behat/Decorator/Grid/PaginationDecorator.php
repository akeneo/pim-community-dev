<?php

namespace Pim\Behat\Decorator\Grid;

use Behat\Mink\Element\NodeElement;
use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

/**
 * Decorator to add pagination features to an element
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PaginationDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    /** @var array selectors for pagination components*/
    protected $selectors = [
        'pagination input' => '.AknActionButton--input',
        'page size button' => '.page-size .AknActionButton',
        'page size list'   => '.page-size .AknDropdown-menu',
        'page size items'  => '.page-size .AknDropdown-menuLink',
        'pagination last page'   => '.AknGridToolbar-center .AknGridToolbar-actionButton:last-child span',
    ];

    /**
     * Get the page number
     *
     * @return int
     */
    public function getPageNumber()
    {
        return (int) $this->getPaginationField()->getValue();
    }

    /**
     * Set the grid page number
     *
     * @param mixed $num
     */
    public function setPageNumber($num)
    {
        $this->spin(function () use ($num) {
            $pagination = $this->getPaginationField();
            $pagination->setValue($num);
            $pagination->blur();

            return true;
        }, 'Can not set page number!');
    }

    /**
     * Get the page size
     *
     * @return int
     */
    public function getPageSize()
    {
        preg_match('/^\d+/', $this->getPageSizeButton()->getHtml(), $size);
        return (int) $size[0];
    }

    /**
     * Get the Last page
     *
     * @return int
     */
    public function getLastPage()
    {
        preg_match('/^\d+/', $this->getLastPageButton()->getHtml(), $lastPage);
        return (int) $lastPage[0];
    }

    /**
     * Set the page size
     *
     * @param mixed $num
     */
    public function setPageSize($num)
    {
        $this->spin(function () use ($num) {
            $button = $this->getPageSizeButton();
            if (!$button->isVisible()) {
                return false;
            }
            $button->click();

            $item = null;
            $items = $this->findAll('css', $this->selectors['page size items']);
            foreach ($items as $link) {
                if (null === $item && $link->getText() === strval($num) && $link->isVisible()) {
                    $item = $link;
                }
            }
            if (null === $item) {
                return false;
            }
            $item->click();

            return $this->getPageSize() === (int) $num;
        }, sprintf('The pagination button was not updated with "%s"', $num));
    }

    /**
     * Get the pagination element
     *
     * @return NodeElement
     */
    protected function getPaginationField()
    {
        return $this->spin(function () {
            return $this->find('css', $this->selectors['pagination input']);
        }, 'Cannot find the pagination filter');
    }

    /**
     * Get the button element managing the size
     *
     * @return NodeElement
     */
    protected function getPageSizeButton()
    {
        return $this->spin(function () {
            return $this->find('css', $this->selectors['page size button']);
        }, 'Cannot find the change page size button');
    }

    /**
     * Get the button element of the last page
     *
     * @return NodeElement
     */
    protected function getLastPageButton()
    {
        return $this->spin(function () {
            return $this->find('css', $this->selectors['pagination last page']);
        }, 'Cannot find the button for the last page');
    }
}
