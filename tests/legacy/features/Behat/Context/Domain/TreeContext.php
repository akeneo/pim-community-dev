<?php

namespace Pim\Behat\Context\Domain;

use Behat\Mink\Exception\ExpectationException;
use Context\Spin\SpinCapableTrait;
use PHPUnit\Framework\Assert;
use Pim\Behat\Context\PimContext;

class TreeContext extends PimContext
{
    use SpinCapableTrait;

    /**
     * @param string $tree
     *
     * @Given /^I select the "([^"]*)" tree$/
     */
    public function iSelectTheTree($tree)
    {
        $this->spin(function () use ($tree) {
            return $this->getCurrentPage()->selectTree($tree);
        }, sprintf('Cannot select tree "%s"', $tree));
    }

    /**
     * @param string $node
     *
     * @Given /^I expand the "([^"]*)" category$/
     */
    public function iExpandTheNode($node)
    {
        $this->getElementOnCurrentPage('Category tree')
            ->expandNode($node);
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
        $this->wait(); //TODO remove this wait
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
        $categoryTree = $this->getElementOnCurrentPage('Category tree');

        $parentNode = $this->spin(function () use ($parent, $categoryTree) {
            $parentNode = $categoryTree->findNodeInTree($parent);

            if (!$parentNode->isOpen()) {
                $parentNode->open();
            }

            return $parentNode;
        }, sprintf('Cannot find parent node "%s" of "%s"', $parent, $child));

        $childNode = $parentNode->find('css', sprintf('li[data-code="%s"]', $child));

        if ($not && $childNode) {
            throw $this->getMainContext()->createExpectationException(
                sprintf('Expecting not to see category "%s" under the category "%s"', $child, $parent)
            );
        }

        if (null === $not && null === $childNode) {
            throw $this->getMainContext()->createExpectationException(
                sprintf('Expecting to see category "%s" under the category "%s", not found', $child, $parent)
            );
        }
    }

    /**
     * @param string $tree
     *
     * @Then /^The tree "([^"]*)" should be open$/
     */
    public function theTreeShouldBeOpen($tree)
    {
        $categoryTree = $this->getElementOnCurrentPage('Category tree');
        $openTree = $categoryTree->findOpenTree();

        if ($openTree !== $tree) {
            throw $this->getMainContext()->createExpectationException(
                sprintf('Expecting to see tree "%s" open, found "%s".', $tree, $openTree)
            );
        }
    }

    /**
     * @Given /^I blur the category node$/
     */
    public function iBlurTheNode()
    {
        $this->getCurrentPage()->find('css', '#container')->click();
    }

    /**
     * TODO This method should be refactored because we have a spin checking if "checkbox" is present. If not, we wait
     *      X seconds for nothing.
     *
     * @param string $right
     * @param string $node
     *
     * @Given /^I (right )?click on the "([^"]*)" category$/
     */
    public function iClickOnTheNode($right, $node)
    {
        $node = $this->getElementOnCurrentPage('Category tree')
            ->findNodeInTree($node);

        if ($right) {
            $node->rightClick();
        } else {
            $node->select();
            $this->wait();
        }
    }

    /**
     * @Given /^the "([^"]*)" category node should be selected/
     */
    public function theNodeShouldBeChecked($code)
    {
        $categoryTree = $this->getElementOnCurrentPage('Category tree');

        $node = $categoryTree->findNodeInTree($code);

        Assert::assertNotNull($node);
        Assert::assertTrue($node->isSelected());
    }

    /**
     * @Given /^the "([^"]*)" category node should not be selected/
     */
    public function theNodeShouldNotBeChecked($code)
    {
        $categoryTree = $this->getElementOnCurrentPage('Category tree');

        $node = $categoryTree->findNodeInTree($code);

        Assert::assertNotNull($node);
        Assert::assertFalse($node->isSelected());
    }
}
