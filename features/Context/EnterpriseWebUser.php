<?php

namespace Context;

use Behat\Behat\Context\Step;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Element\NodeElement;
use Context\WebUser as BaseWebUser;

/**
 * Overrided context
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class EnterpriseWebUser extends BaseWebUser
{
    /**
     * Override parent
     *
     * {@inheritdoc}
     */
    public function iChooseTheOperation($operation)
    {
        $this->getNavigationContext()->currentPage = $this
            ->getPage('Batch Operation')
            ->addStep('Publish products', 'Batch Publish')
            ->addStep('Unpublish products', 'Batch Unpublish')
            ->chooseOperation($operation)
            ->next();

        $this->wait();
    }

    /**
     * @Given /^I should not see a single form input$/
     */
    public function iShouldNotSeeASingleFormInput()
    {
        new Step\Given('I should not see an "input" element');
    }

    /**
     * @param string $fieldName
     * @param string $expected
     *
     * @Then /^the view mode field (.*) should contain "([^"]*)"$/
     */
    public function theProductViewModeFieldValueShouldBe($fieldName, $expected = '')
    {
        $field = $this->getCurrentPage()->findField($fieldName);
        $actual = trim($field->getHtml());

        if ($expected != $actual) {
            throw $this->createExpectationException(
                sprintf(
                    'Expected product view mode field "%s" to contain "%s", but got "%s".',
                    $fieldName,
                    $expected,
                    $actual
                )
            );
        }
    }

    /**
     * @Then /^I should see the smart attribute tooltip$/
     */
    public function iShouldSeeTheTooltip()
    {
        if ($this->getSession()->getDriver() instanceof Selenium2Driver) {
            $script = 'return $(\'.icon-code-fork[data-async-content]\').length > 0';
            $found = $this->getSession()->evaluateScript($script);
            if ($found) {
                return;
            }
            throw $this->createExpectationException(
                sprintf(
                    'Expecting to see smart attribute tooltip'
                )
            );
        }
    }

    /**
     * @param TableNode $table
     *
     * @Then /^the grid locale switcher should contain the following items:$/
     */
    public function theGridLocaleSwitcherShouldContainTheFollowingItems(TableNode $table, $page = 'index')
    {
        return parent::theLocaleSwitcherShouldContainTheFollowingItems($table, $page);
    }

    /**
     * @param string $date
     *
     * @When /^I change the end of use at to "([^"]+)"$/
     */
    public function iChangeTheEndOfUseAtTo($date)
    {
        $this->getCurrentPage()->changeTheEndOfUseAtTo($date);
    }

    /**
     * @params string       $field
     * $params string|array $tags
     *
     * @Given /^I add the following tags? in the "([^"]+)" select2 : ([^"]+)$/
     */
    public function iAddTheFollowingTagsInTheSelect2($field, $tags)
    {
        if (is_string($tags)) {
            $tags = $this->convertCommaSeparatedToArray($tags);
        }

        $select2 = $this->getCurrentPage()->findField($field);
        $search  = $this->getCurrentPage()->find('css', '.select2-results');
        foreach ($tags as $tag) {
            $select2->click();
            // Impossible to use NodeElement::setValue() since the Selenium2 implementation emulates the change event
            // by hitting the TAB key, which results in closing select2 choices
            $this->getSession()->executeScript(
                sprintf('$(\'.select2-search-field .select2-input\').val(\'%s\').trigger(\'paste\');', $tag)
            );

            $item = $this->spin(function () use ($search, $tag) {
                return $search->find(
                    'css',
                    sprintf('.select2-result:not(.select2-selected) .select2-result-label:contains("%s")', $tag)
                );
            });
            $item->click();
        }
    }

    /**
     * @params string       $field
     * $params string|array $tags
     *
     * @Given /^I set the following tags? in the "([^"]+)" select2 : ([^"]+)$/
     */
    public function iSetTheFollowingTagsInTheSelect2($field, $tags)
    {
        if (is_string($tags)) {
            $tags = $this->convertCommaSeparatedToArray($tags);
        }

        $choices = $this->getSelect2Choices($field);
        $this->removeTags($choices);

        $this->iAddTheFollowingTagsInTheSelect2($field, $tags);
    }

    /**
     * @params string       $field
     * @params string|array $tags
     *
     * @Given /^I remove the following tags? from the "([^"]+)" select2 : ([^"]+)$/
     */
    public function iRemoveTheFollowingTagsFromTheSelect2($field, $tags)
    {
        $tags = $this->convertCommaSeparatedToArray($tags);
        $choices = $this->getSelect2Choices($field, $tags);
        $this->removeTags($choices);
    }

    /**
     * @params string        $field
     * @params string[]|null $tags
     *
     * @return NodeElement[]|array NodeElement[] if something is found or empty array if nothing is found
     */
    protected function getSelect2Choices($field, $tags = null)
    {
        $select2Label   = $this->getCurrentPage()->find('css', sprintf('label:contains("%s")', $field));
        $currentChoices = $select2Label->getParent()->findAll('css', '.select2-search-choice');

        if (null !== $tags) {
            $choices = [];
            foreach ($tags as $tag) {
                foreach ($currentChoices as $choice) {
                    if ($choice->getText() === $tag) {
                        $choices[] = $choice;
                        break;
                    }
                }
            }

            return $choices;
        }

        return $currentChoices;
    }

    /**
     * Remove tags from their NodeElement. You can use getSelect2Choices() to found their NodeElement
     *
     * @param NodeElement[] $tagElements
     */
    protected function removeTags(array $tagElements)
    {
        foreach ($tagElements as $tag) {
            $removeLink = $tag->find('css', '.select2-search-choice-close');
            $removeLink->click();

            $this->spin(function () use ($removeLink) {
                try {
                    $removeLink->getText();
                } catch (\Exception $e) {
                    return true;
                }

                return false;
            });
        }
    }

    /**
     * @param string $vars Vars separated by ',' or ', '
     *
     * @return string[]
     */
    protected function convertCommaSeparatedToArray($vars)
    {
        $exploded = explode(',', $vars);

        return array_map(function ($var) {
            return trim($var);
        }, $exploded);
    }

    /**
     * @Given /^I wait for the published product quick export to finish$/
     */
    public function iWaitForThePublishedProductQuickExportToFinish()
    {
        $this->waitForMassEditJobToFinish('csv_published_product_quick_export');
    }

    /**
     * @param string $button
     *
     * @Given /^I press the Send for approval button$/
     */
    public function iPressTheSendForApprovalButton()
    {
        $this->iPressTheButton("Send for approval");
        $this->iPressTheButton("Send");
    }

    /**
     * @Given /^I fill in this comment in the popin: "([^"]+)"$/
     * @Given /^I fill in this comment in the popin:$/
     */
    public function iFillInThisCommentInThePopin($comment)
    {
        if ($comment instanceof PyStringNode) {
            $comment = $comment->getRaw();
        }

        $this->getCurrentPage()->simpleFillField('modal-comment', $comment);
        $this->getSession()->executeScript("$('#modal-comment').trigger('change');");
    }

    /**
     * @param string    $partialAction
     * @param TableNode $table
     *
     * @Given /^I partially (approve|reject):$/
     *
     * @throws Spin\TimeoutException
     * @throws \Exception
     */
    public function iPartiallyApproveReject($partialAction, TableNode $table)
    {
        $hash          = $table->getHash();
        $partialButton = sprintf('.partial-%s-link', $partialAction);

        foreach ($hash as $row) {
            $button = $this->getElementByDataAttribute($row, $partialButton);
            $button->click();

            $comment = isset($row['comment']) ? $row['comment'] : '';

            $this->iFillInThisCommentInThePopin($comment);
            $this->iPressTheButtonInThePopin("Send");
        }
    }

    /**
     * @Then /^I should not see the following partial approve buttons?:$/
     *
     * @param TableNode $table
     */
    public function iShouldNotSeeTheFollowingPartialApproveButtons(TableNode $table)
    {
        $this->iShouldSeeTheFollowingPartialApproveButtons($table, true);
    }

    /**
     * @Then /^I should see the following partial approve buttons?:$/
     *
     * @param bool      $not
     * @param TableNode $table
     *
     * @throws \Exception
     */
    public function iShouldSeeTheFollowingPartialApproveButtons(TableNode $table, $not = false)
    {
        $hash = $table->getHash();

        foreach ($hash as $row) {
            try {
                $approveButton = $this->getElementByDataAttribute($row, '.partial-approve-link');
            } catch (\Exception $e) {
                $approveButton = null;
            }

            if ($not && $approveButton !== null && $approveButton->isVisible()) {
                throw new \Exception(
                    sprintf(
                        'Partial approve button is visible, but it should not (%s)',
                        json_encode($row)
                    )
                );
            }

            if (!$not && ($approveButton === null || !$approveButton->isVisible())) {
                throw new \Exception(
                    sprintf(
                        'Partial approve button is not visible, but it should (%s)',
                        json_encode($row)
                    )
                );
            }
        }
    }

    /**
     * @Then /^I should not see the following changes on the proposals?:$/
     *
     * @param TableNode $table
     */
    public function iShouldNotSeeTheFollowingChanges(TableNode $table)
    {
        $this->iShouldSeeTheFollowingChanges($table, true);
    }

    /**
     * @Then /^I should see the following changes on the proposals?:$/
     *
     * @param TableNode $table
     * @param bool      $not
     *
     * @throws \Exception
     */
    public function iShouldSeeTheFollowingChanges(TableNode $table, $not = false)
    {
        $hash = $table->getHash();

        foreach ($hash as $data) {
            try {
                $row = $this->getElementByDataAttribute($data, '.proposal-changes');
            } catch (\Exception $e) {
                $row = null;
            }

            if ($not && $row !== null && $row->isVisible()) {
                throw new \Exception(
                    sprintf(
                        'Partial change is visible, but it should not (%s)',
                        json_encode($data)
                    )
                );
            }

            if (!$not && ($row === null || !$row->isVisible())) {
                throw new \Exception(
                    sprintf(
                        'Partial change is not visible, but it should (%s)',
                        json_encode($data)
                    )
                );
            }
        }
    }

    /**
     * Get the NodeElement to partially approve a proposal, identified by the given $data
     *
     * @param array  $data    ['product' => '', 'attribute' => '', 'author' => '', 'scope' => '', 'locale' => '']
     * @param string $context ".proposal-changes" for example
     *
     * @throws Spin\TimeoutException
     * @throws \Exception
     *
     * @return NodeElement
     */
    protected function getElementByDataAttribute($data, $context)
    {
        $locator = sprintf('%s[data-product="%s"][data-attribute="%s"][data-author="%s"]',
            $context,
            $data['product'],
            $data['attribute'],
            $data['author']
        );

        $locator .= (isset($data['scope']) && '' !== $data['scope']) ? sprintf('[data-scope="%s"]', $data['scope']) : '';
        $locator .= (isset($data['locale']) && '' !== $data['locale']) ? sprintf('[data-locale="%s"]', $data['locale']) : '';

        return $this->spin(function () use ($locator) {
            return $this->getCurrentPage()->find('css', $locator);
        }, sprintf('Element "%s" has not been found in the page.', $locator));
    }
}
