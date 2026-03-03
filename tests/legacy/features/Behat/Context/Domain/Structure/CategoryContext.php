<?php

declare(strict_types=1);

namespace Pim\Behat\Context\Domain\Structure;

use Behat\Mink\Element\NodeElement;
use Context\Spin\SpinCapableTrait;
use Pim\Behat\Context\PimContext;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CategoryContext extends PimContext
{
    use SpinCapableTrait;

    /**
     * @When I follow the :categoryLabel category
     */
    public function iFollowTheCategory(string $categoryLabel)
    {
        /** @var NodeElement $categoryTree */
        $categoryTree = $this->spin(function () use ($categoryLabel) {
            return $this->getCurrentPage()->find('named', array('content', $categoryLabel));
        }, sprintf('The "%s" category was not found', $categoryLabel));

        $this->spin(function () use ($categoryTree) {
            $categoryTree->press();
            return true;
        }, 'Can not follow the "%s" category');
    }

    /**
     * @When I follow the :categoryTreeLabel category tree
     */
    public function iFollowTheCategoryTree(string $categoryTreeLabel)
    {
        /** @var NodeElement $categoryTree */
        $categoryTree = $this->spin(function () use ($categoryTreeLabel) {
            $treeList = $this->getCurrentPage()->find('css', 'table');
            if (!$treeList) {
                return false;
            }
            return $treeList->find('named', array('content', $categoryTreeLabel));
        }, sprintf('The "%s" category tree was not found', $categoryTreeLabel));

        $this->spin(function () use ($categoryTree) {
            $categoryTree->press();
            return true;
        }, 'Can not follow the "%s" category tree');
    }

    /**
     * @When I hover over the category :categoryLabel
     */
    public function iHoverOverTheCategory(string $categoryLabel)
    {
        /** @var NodeElement $category */
        $category = $this->spin(function () use ($categoryLabel) {
            return $this->getCurrentPage()->find('named', array('content', $categoryLabel));
        }, sprintf('The "%s" category was not found', $categoryLabel));

        $this->spin(function () use ($category) {
            $category->mouseOver();
            return true;
        }, 'Can not hover the "%s" category');
    }

    /**
     * @When I hover over the category tree item :categoryLabel
     */
    public function iHoverOverTheCategoryTreeItem(string $categoryLabel)
    {
        /** @var NodeElement $categoryTree */
        $categoryTree = $this->spin(function () use ($categoryLabel) {
            $tree = $this->getCurrentPage()->find('css', 'ul[role="tree"]');
            if (!$tree) {
                return false;
            }

            return $tree->find('named', array('content', $categoryLabel));
        }, sprintf('The "%s" category was not found', $categoryLabel));

        $this->spin(function () use ($categoryTree) {
            $categoryTree->mouseOver();
            return true;
        }, 'Can not hover the "%s" category tree');
    }

    /** @When I create the category with code :code */
    public function iCreateTheCategoryWithCode(string $code)
    {
        $this->spin(function () use ($code) {
            $modal = $this->getCurrentPage()->find('css', 'div[role=dialog]');
            if (!$modal) {
                return false;
            }

            $modal->fillField('Code', $code);
            $modal->findButton('Create')->click();
            return true;
        }, sprintf('Can not create the category with code %s', $code));
    }

    /**
     * @When I open the category tab :tabName
     */
    public function iOpenTheCategoryTab(string $tabName)
    {
        /** @var NodeElement $tab */
        $tab = $this->spin(function () use ($tabName) {
            return $this->getCurrentPage()->find('css', 'div[role=tab]:contains('.$tabName.')');
        }, sprintf('Tab "%s" not found', $tabName));

        $tab->click();
    }

    /**
     * @When I submit the category changes
     */
    public function iSubmitTheCategoryChanges()
    {
        $this->spin(function () {
            $saveButton = $this->getCurrentPage()->findButton('Save');
            if (!$saveButton) {
                return false;
            }

            $saveButton->press();
            return true;
        }, 'Can not save the current category');

        // Wait for the server response
        $this->spin(function () {
            return $this->getCurrentPage()->find('css', 'div[role=status]');
        }, 'No response for the category update');
    }
}
