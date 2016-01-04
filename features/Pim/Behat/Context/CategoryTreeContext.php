<?php

namespace Pim\Behat\Context;

use Pim\Behat\Context\PimContext;

class CategoryTreeContext extends PimContext
{
    /**
     * @param string $category
     *
     * @Given /^I select the "([^"]*)" tree$/
     */
    public function iSelectTheTree($category)
    {
        $this->getCurrentPage()->selectTree($category);
        $this->wait();
    }

    /**
     * @param string $category
     *
     * @Given /^I expand the "([^"]*)" category$/
     */
    public function iExpandTheCategory($category)
    {
        //TODO: we should spin here
        $this->wait(); // Make sure that the tree is loaded
        $this->getCurrentPage()->expandCategory($category);
        $this->wait();
    }

    /**
     * @param string $category1
     * @param string $category2
     *
     * @Given /^I drag the "([^"]*)" category to the "([^"]*)" category$/
     */
    public function iDragTheCategoryToTheCategory($category1, $category2)
    {
        $this->getCurrentPage()->dragCategoryTo($category1, $category2);
        $this->wait();
    }

    /**
     * @param string $not
     * @param string $child
     * @param string $parent
     *
     * @Then /^I should (not )?see the "([^"]*)" category under the "([^"]*)" category$/
     *
     * @throws ExpectationException
     */
    public function iShouldSeeTheCategoryUnderTheCategory($not, $child, $parent)
    {
        $this->wait(); // Make sure that the tree is loaded

        $parentNode = $this->getCurrentPage()->findCategoryInTree($parent);
        $childNode  = $parentNode->getParent()->find('css', sprintf('li a:contains("%s")', $child));

        if ($not && $childNode) {
            throw $this->createExpectationException(
                sprintf('Expecting not to see category "%s" under the category "%s"', $child, $parent)
            );
        }

        if (!$not && !$childNode) {
            throw $this->createExpectationException(
                sprintf('Expecting to see category "%s" under the category "%s", not found', $child, $parent)
            );
        }
    }

    /**
     * @param string $right
     * @param string $category
     *
     * @Given /^I (right )?click on the "([^"]*)" category$/
     */
    public function iClickOnTheCategory($right, $category)
    {
        $category = $this->getCurrentPage()->findCategoryInTree($category);

        if ($right) {
            $category->rightClick();
        } else {
            try {
                $checkbox = $this->spin(function () use ($category) {
                    return $category->find('css', '.jstree-checkbox');

                });
            } catch (\Exception $e) {
                $checkbox = null;
            }

            if (null !== $checkbox) {
                $checkbox->click();
            } else {
                $category->click();
            }
            $this->wait();
        }
    }

    /**
     * @Then /^I should see (\d+) category count$/
     *
     * @param int $count
     *
     * @throws ExpectationException
     */
    public function iShouldSeeCategoryCount($count)
    {
        $badge = $this->getCurrentPage()->find('css', sprintf('span.badge:contains("%d")', $count));
        if (!$badge) {
            throw $this->createExpectationException('Catgeroy badge not found');
        }
    }

    /**
     * @Given /^I blur the category node$/
     */
    public function iBlurTheCategoryNode()
    {
        $this->getCurrentPage()->find('css', '#container')->click();
        $this->wait();
    }
}
