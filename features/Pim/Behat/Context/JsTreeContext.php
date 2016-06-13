<?php

namespace Pim\Behat\Context;

/**
 * Class JsTreeContext
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JsTreeContext extends PimContext
{
    /**
     * @Given /^the "([^"]*)" jstree node should be checked$/
     */
    public function theJstreeNodeShouldBeChecked($code)
    {
        $node = $this->getCurrentPage()->getElement('jsTree')->findNodeInTree($code);

        assertNotNull($node);
        assertTrue($node->isChecked());
    }

    /**
     * @Given /^the "([^"]*)" jstree node should not be checked$/
     */
    public function theJstreeNodeShouldNotBeChecked($code)
    {
        $node = $this->getCurrentPage()->getElement('jsTree')->findNodeInTree($code);

        assertNotNull($node);
        assertFalse($node->isChecked());
    }

    /**
     * @When /^I check the jstree node "([^"]*)"$/
     */
    public function iCheckTheJstreeNode($code)
    {
        $node = $this->getCurrentPage()->getElement('jsTree')->findNodeInTree($code);

        assertNotNull($node);
        assertFalse($node->isChecked());

        $node->select();
    }

    /**
     * @When /^I uncheck the jstree node "([^"]*)"$/
     */
    public function iUncheckTheJstreeNode($code)
    {
        $node = $this->getCurrentPage()->getElement('jsTree')->findNodeInTree($code);

        assertNotNull($node);
        assertTrue($node->isChecked());

        $node->select();
    }
}
