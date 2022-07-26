<?php

namespace Context\Page\Product;

use Akeneo\Category\Infrastructure\Component\Model\Category;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ElementNotFoundException;
use Context\Page\Base\Grid;
use Pim\Behat\Decorator\ContextSwitcherDecorator;
use Pim\Behat\Decorator\Tree\TreeDecorator;

/**
 * Product index page
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Index extends Grid
{
    /**
     * @var string
     */
    protected $path = '#/enrich/product/';

    /**
     * {@inheritdoc}
     */
    public function __construct($session, $pageFactory, $parameters = [])
    {
        parent::__construct($session, $pageFactory, $parameters);

        $this->elements = array_merge(
            $this->elements,
            [
                'Categories tree'         => ['css' => '#tree'],
                'Main context selector'   => [
                    'css'        => '.AknColumn-innerTop',
                    'decorators' => [ContextSwitcherDecorator::class],
                ],
                'Tree select'             => ['css' => '#tree [aria-haspopup="listbox"] button'],
                'Locales dropdown'        => ['css' => '#locale-switcher'],
                'Sidebar collapse button' => ['css' => '.sidebar .sidebar-controls i.icon-double-angle-left'],
                'Sidebar expand button'   => ['css' => '.separator.collapsed i.icon-double-angle-right'],
                'Manage filters options'  => ['css' => '.filter-list.select-filter-widget .ui-multiselect-checkboxes li label span'],
                'Category tree'           => [
                    'css'        => '#tree',
                    'decorators' => [ TreeDecorator::class ]
                ]
            ]
        );
    }

    /**
     * @return int
     */
    public function countLocaleLinks()
    {
        return count($this->getElement('Locales dropdown')->findAll('css', 'li [data-locale]'));
    }

    /**
     * @param string $locale locale code
     * @param string $locale locale label
     * @param string $flag   class of the flag icon
     *
     * @throws ElementNotFoundException
     *
     * @return NodeElement|null
     */
    public function findLocaleLink($locale, $label, $flag = null)
    {
        $link = $this->getElement('Locales dropdown')
            ->find('css', sprintf('li > a[href="#/enrich/product/?dataLocale=%s"]', $locale));

        if (!$link) {
            throw new ElementNotFoundException(
                $this->getSession(),
                sprintf('Locale %s link', $locale)
            );
        }

        if ($flag) {
            $flagElement = $link->find('css', 'span.flag-language i');
            if (!$flagElement) {
                throw new ElementNotFoundException(
                    $this->getSession(),
                    sprintf('Flag not found for locale %s link', $locale)
                );
            }
            if (strpos($flagElement->getAttribute('class'), $flag) === false) {
                return null;
            }
        }

        return $link;
    }

    /**
     * @param string $category
     *
     * @return Index
     */
    public function selectTree($category)
    {
        if (!$this->find('css', '#dropdown-root [role="listbox"]')) {
            $button = $this->getElement('Tree select');
            $button->click();
            $this->spin(function () {
                return $this->find('css', '[role="listbox"]');
            }, 'Can not open Tree Selector');
        }

        $matchingCategoryTree = null;
        foreach ($this->findAll('css', '#dropdown-root [role="option"]') as $options) {
            if (str_starts_with($options->getText(), $category)) {
                $matchingCategoryTree = $options;
            }
        }
        if (null === $matchingCategoryTree) {
            throw new ElementNotFoundException($this->getDriver(), sprintf('Category tree %s', $category));
        }
        $matchingCategoryTree->click();

        return $this;
    }

    public function findField($locator)
    {
        if ($locator === 'Include sub-categories') {
            return $this->spin(function () {
                return $this->find('css', '#tree [role="switch"]');
            }, 'Can not find Include sub-categories switch');
        }

        return parent::findField($locator);
    }

    /**
     * @param Category $category
     *
     * @throws \Exception
     */
    public function clickCategoryFilterLink($category)
    {
        $elt = $this
            ->getElement('Categories tree')
            ->find('css', sprintf('#node_%s a', $category->getId()));

        if (!$elt) {
            throw new \Exception(sprintf('Could not find category filter "%s".', $category->getId()));
        }

        $elt->click();
    }

    /**
     * Filter by unclassified products
     */
    public function clickUnclassifiedCategoryFilterLink()
    {
        $elt = $this
            ->getElement('Categories tree')
            ->find('css', sprintf('#node_-1 a'));

        if (!$elt) {
            throw new \Exception(sprintf('Could not find unclassified category filter.'));
        }

        $elt->click();
    }

    /**
     * Returns list of filters available from "Manage filters" select/dropdown
     *
     * @return NodeElement[]
     */
    public function getFiltersList()
    {
        return $this->spin(function () {
            return $this->findAll('css', $this->elements['Manage filters options']['css']);
        }, 'Filters list was not found.');
    }

    /**
     * {@inheritdoc}
     *
     * This method is overridden in this class because we have to wait modal to be display before continue
     */
    public function clickCreationLink()
    {
        $this->spin(function () {
            $modal = $this->find('css', '.modal-backdrop');

            if (null !== $modal && $modal->isVisible()) {
                return true;
            }

            $button = $this->find('css', $this->elements['Creation link']['css']);

            if (null !== $button && $button->isVisible()) {
                $button->click();
            }

            return null;
        }, 'Cannot create product');
    }
}
