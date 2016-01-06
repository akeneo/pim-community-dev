<?php

namespace Pim\Behat\Context\Domain;

use Behat\Mink\Exception\ExpectationException;
use Pim\Behat\Context\PimContext;

class TreeContext extends PimContext
{
    /**
     * @param string $tree
     *
     * @Given /^I select the "([^"]*)" tree$/
     */
    public function iSelectTheTree($tree)
    {
        $this->getCurrentPage()->selectTree($tree);
        $this->wait();
    }

    /**
     * @param string $node
     *
     * @Given /^I expand the "([^"]*)" category$/
     */
    public function iExpandTheNode($node)
    {
        $this->wait(); // Make sure that the tree is loaded
        $this->getCurrentPage()->expandCategory($node);
        $this->wait();
    }

    /**
     * @param string $node1
     * @param string $node2
     *
     * @Given /^I drag the "([^"]*)" category to the "([^"]*)" category$/
     */
    public function iDragTheNodeToTheNode($node1, $node2)
    {
        $this->getCurrentPage()->dragCategoryTo($node1, $node2);
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
    public function iShouldSeeTheNodeUnderTheNode($not, $child, $parent)
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
     * @Given /^I blur the category node$/
     */
    public function iBlurTheNode()
    {
        $this->getCurrentPage()->find('css', '#container')->click();
        $this->wait();
    }

    /**
     * @param string $right
     * @param string $node
     *
     * @Given /^I (right )?click on the "([^"]*)" category$/
     */
    public function iClickOnTheNode($right, $node)
    {
        $node = $this->getCurrentPage()->findCategoryInTree($node);

        if ($right) {
            $node->rightClick();
        } else {
            try {
                $checkbox = $this->spin(function () use ($node) {
                    return $node->find('css', '.jstree-checkbox');

                });
            } catch (\Exception $e) {
                $checkbox = null;
            }

            if (null !== $checkbox) {
                $checkbox->click();
            } else {
                $node->click();
            }
            $this->wait();
        }
    }
}
