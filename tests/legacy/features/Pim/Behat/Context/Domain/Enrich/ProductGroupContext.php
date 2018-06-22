<?php

namespace Pim\Behat\Context\Domain\Enrich;

use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ExpectationException;
use Context\Spin\SpinCapableTrait;
use PHPUnit\Framework\Assert;
use Pim\Behat\Context\PimContext;

class ProductGroupContext extends PimContext
{
    use SpinCapableTrait;

    /**
     * @param array|string $data
     *
     * @return GroupInterface
     *
     * @Given /^a "([^"]*)" product group$/
     */
    public function createGroup($data)
    {
        if (is_string($data)) {
            $data = ['code' => $data];
        }

        $converter = $this->getService('pim_connector.array_converter.flat_to_standard.group');
        $processor = $this->getService('pim_connector.processor.denormalization.group');
        $convertedData = $converter->convert($data);
        $group = $processor->process($convertedData);
        $this->saveGroup($group);

        return $group;
    }

    /**
     * @param TableNode $table
     *
     * @Given /^the following product group values?:$/
     */
    public function theFollowingGroupValues(TableNode $table)
    {
        $groups = [];

        foreach ($table->getHash() as $row) {
            $row = array_merge(['locale' => null, 'scope' => null, 'value' => null], $row);

            $attributeCode = $row['attribute'];
            if ($row['locale']) {
                $attributeCode .= '-' . $row['locale'];
            }
            if ($row['scope']) {
                $attributeCode .= '-' . $row['scope'];
            }
            $groups[$row['group']][$attributeCode] = $this->replacePlaceholders($row['value']);
        }

        foreach ($groups as $code => $data) {
            if (!isset($data['type'])) {
                $data['type'] = 'VARIANT';
            }
            $this->createGroup(['code' => $code] + $data);
        }
    }

    /**
     * @param string $identifier
     *
     * @Given /^I am on the "([^"]*)" product group page$/
     * @Given /^I edit the "([^"]*)" product group$/
     */
    public function iAmOnTheGroupEditPage($identifier)
    {
        $page   = 'ProductGroup';
        $entity = $this->getFixturesContext()->getProductGroup($identifier);
        $this->getNavigationContext()->openPage(sprintf('%s edit', $page), ['code' => $entity->getCode()]);
    }

    /**
     * @param GroupInterface $group
     *
     * @Given /^I should be on the ("([^"]*)" product group) page$/
     */
    public function iShouldBeOnTheGroupPage(GroupInterface $group)
    {
        $expectedAddress = $this->getPage('ProductGroup edit')->getUrl(['code' => $group->getCode()]);
        $this->getNavigationContext()->assertAddress($expectedAddress);
    }

    /**
     * @Given /^I select the "([^"]*)" product group$/
     */
    public function iSelectGroup($group)
    {
        $this->getCurrentPage()->fillField('Group', $group);
    }

    /**
     * @param string $label
     * @param string $value
     *
     * @Given /^I fill in the product group property "([^"]*)" with "([^"]*)"$/
     */
    public function iFillInTheGroupPropertyWith($label, $value)
    {
        $this->findPropertyFieldByLabel($label)->setValue($value);
    }


    /**
     * @param string $field
     *
     * @Then /^the product group property "([^"]*)" should be disabled$/
     *
     * @throws ExpectationException
     */
    public function theProductGroupPropertyShouldBeDisabled($field)
    {
        Assert::assertTrue(
            $this->findPropertyFieldByLabel($field)->hasAttribute('disabled'),
            sprintf('Expecting field "%s" to be disabled.', $field)
        );
    }

    /**
     * @param string $label
     *
     * @return NodeElement
     */
    protected function findPropertyFieldByLabel($label)
    {
        $node = $this->spin(function () use ($label) {
            return $this->getSession()->getPage()->find('css', sprintf('label:contains("%s")', $label));
        }, sprintf('Unable to find a label containing "%s"', $label));

        Assert::assertTrue($node->hasAttribute('for'));

        return $this->spin(function () use ($node) {
            return $this->getSession()->getPage()->find('css', sprintf('#%s', $node->getAttribute('for')));
        }, sprintf('Unable to find element with id "%s"', $node->getAttribute('for')));
    }

    /**
     * @param GroupInterface $group
     */
    private function saveGroup(GroupInterface $group)
    {
        $saver = $this->getService('pim_catalog.saver.group');
        $saver->save($group);
    }
}
