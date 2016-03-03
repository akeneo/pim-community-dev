<?php

namespace Pim\Behat\Decorator\GridDecorator;

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
        'pagination input' => '.icons-holder input[type="text"]',
        'page size button' => '.page-size .dropdown-toggle',
        'page size list'   => '.page-size .dropdown-menu',
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
        $pagination = $this->getPaginationField();
        $pagination->setValue($num);
        $pagination->blur();
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
     * Set the page size
     *
     * @param mixed $num
     */
    public function setPageSize($num)
    {
        $this->getPageSizeButton()->click();

        $list = $this->spin(function () {
            return $this->find('css', $this->selectors['page size list']);
        }, 'Cannot find the change page size list');

        $list->find('css', sprintf('li a:contains("%d")', (int) $num))->click();
    }

    /**
     * Get the pagination element
     *
     * @return mixed
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
     * @return mixed
     */
    protected function getPageSizeButton()
    {
        return $this->spin(function () {
            return $this->find('css', $this->selectors['page size button']);
        }, 'Cannot find the change page size button');
    }
}
