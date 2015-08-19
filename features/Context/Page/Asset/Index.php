<?php

namespace Context\Page\Asset;

use Behat\Mink\Element\Element;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ElementNotFoundException;
use Context\Page\Base\Grid;

/**
 * Product assets index page
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Index extends Grid
{
    /** @var string */
    protected $path = '/enrich/asset/';

    /**
     * {@inheritdoc}
     */
    public function __construct($session, $pageFactory, $parameters = [])
    {
        parent::__construct($session, $pageFactory, $parameters);

        $this->elements = array_merge(
            $this->elements,
            [
                'Category tree' => ['css' => '#tree'],
                'Tree select'   => ['css' => '#tree_select'],
            ]
        );
    }

    /**
     * @return NodeElement|mixed|null
     */
    public function getDialog()
    {
        return $this->find('css', '.ui-dialog');
    }


    /**
     * @return NodeElement|mixed|null
     */
    public function getLocalizableSwitch()
    {
        return $this->getDialog()->find('css', '.has-switch');
    }

    /**
     * @param string $state Must be 'on' or 'off'
     */
    public function changeLocalizableSwitch($state)
    {
        $switch = $this->getLocalizableSwitch();
        $animationBlock = $switch->find('css', '.switch-animate');
        if (!$animationBlock->hasClass(sprintf('switch-%s', $state))) {
            $animationBlock->find('css', 'label.switch-small')->click();
        }
        $referenceField = $this->find('css', '.reference-field');

        if ('on' === $state) {
            $this->spin(function () use ($referenceField) {
                return !$referenceField->isVisible();
            });
        } else {
            $this->spin(function () use ($referenceField) {
                return $referenceField->isVisible();
            });
        }
    }

    /**
     * @throws ElementNotFoundException
     *
     * @return Element
     */
    public function findReferenceUploadZone()
    {
        $uploadZone = $this->getDialog()->find('css', '.reference-field .asset-uploader');

        if (!$uploadZone) {
            throw new ElementNotFoundException($this->getSession(), 'reference upload zone');
        }

        return $uploadZone;
    }

    /**
     * @param string $category
     *
     * @return Index
     */
    public function selectTree($category)
    {
        $this->getElement('Tree select')->selectOption($category);

        return $this;
    }

    /**
     * @param Category $category
     *
     * @throws \Exception
     */
    public function clickCategoryFilterLink($category)
    {
        $elt = $this
            ->getElement('Category tree')
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
            ->getElement('Category tree')
            ->find('css', sprintf('#node_-1 a'));

        if (!$elt) {
            throw new \Exception(sprintf('Could not find unclassified category filter.'));
        }

        $elt->click();
    }

    /**
     * @param string $category
     *
     * @return CategoryView
     */
    public function expandCategory($category)
    {
        $category = $this->findCategoryInTree($category)->getParent();
        if ($category->hasClass('jstree-closed')) {
            $category->getParent()->find('css', 'ins')->click();
        }

        return $this;
    }

    /**
     * @param string $category
     *
     * @throws \InvalidArgumentException
     *
     * @return NodeElement
     */
    public function findCategoryInTree($category)
    {
        $elt = $this->getElement('Category tree')->find('css', sprintf('li a:contains("%s")', $category));
        if (!$elt) {
            throw new \InvalidArgumentException(sprintf('Unable to find category "%s" in the tree', $category));
        }

        return $elt;
    }
}
