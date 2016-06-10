<?php

namespace Pim\Behat\Context\Domain\Enrich;

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ExpectationException;
use Context\Spin\SpinCapableTrait;
use Pim\Behat\Context\PimContext;
use Pim\Component\Catalog\Model\GroupInterface;

class VariantGroupContext extends PimContext
{
    use SpinCapableTrait;

    /**
     * @param array|string $data
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\Group
     *
     * @Given /^a "([^"]*)" variant group$/
     */
    public function createVariantGroup($data)
    {
        if (is_string($data)) {
            $data = ['code' => $data];
        }

        $converter = $this->getService('pim_connector.array_converter.flat_to_standard.variant_group');
        $processor = $this->getService('pim_connector.processor.denormalization.variant_group');
        $convertedData = $converter->convert($data);
        $variantGroup = $processor->process($convertedData);
        $this->saveVariantGroup($variantGroup);

        return $variantGroup;
    }

    /**
     * @param TableNode $table
     *
     * @Given /^the following variant group values?:$/
     */
    public function theFollowingVariantGroupValues(TableNode $table)
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
            $this->createVariantGroup(['code' => $code] + $data);
        }
    }

    /**
     * @param string $identifier
     *
     * @Given /^I am on the "([^"]*)" variant group page$/
     * @Given /^I edit the "([^"]*)" variant group$/
     */
    public function iAmOnTheVariantGroupEditPage($identifier)
    {
        $page   = 'VariantGroup';
        $entity = $this->getFixturesContext()->getProductGroup($identifier);
        $this->getNavigationContext()->openPage(sprintf('%s edit', $page), ['code' => $entity->getCode()]);
    }

    /**
     * @param GroupInterface $group
     *
     * @Given /^I should be on the ("([^"]*)" variant group) page$/
     */
    public function iShouldBeOnTheVariantGroupPage(GroupInterface $group)
    {
        $expectedAddress = $this->getPage('VariantGroup edit')->getUrl(['code' => $group->getCode()]);
        $this->getNavigationContext()->assertAddress($expectedAddress);
    }

    /**
     * @param string $attribute
     *
     * @throws ExpectationException
     *
     * @return bool
     *
     * @Then /^I should see that (.*) is inherited from variant group attribute$/
     */
    public function iShouldSeeThatAttributeIsInheritedFromVariantGroup($attribute)
    {
        $footer = $this->getCurrentPage()->findFieldFooter($attribute);
        $error = $footer->find('css', '*:contains("Updated by variant group")');

        if (!$error) {
            throw new ExpectationException('Affected by a variant group error was not found', $this->getSession());
        }
    }

    /**
     * @param string $attribute
     *
     * @throws ExpectationException
     *
     * @return bool
     *
     * @Then /^I should see that (.*) is not inherited from variant group attribute$/
     */
    public function iShouldSeeThatAttributeIsNotInheritedFromVariantGroup($attribute)
    {
        $footer = $this->getCurrentPage()->findFieldFooter($attribute);
        $error = $footer->find('css', '*:contains("Updated by variant group")');

        if ($error) {
            throw new ExpectationException('Affected by a variant group error was found', $this->getSession());
        }
    }

    /**
     * @Given /^I select the "([^"]*)" variant group$/
     */
    public function iSelectVariantGroup($variant)
    {
        $this->getCurrentPage()->fillField('Group', $variant);
    }

    /**
     * @param string $field
     *
     * @Then /^the variant group property "([^"Axis]*)" should be disabled$/
     *
     * @throws ExpectationException
     */
    public function theVariantGroupPropertyShouldBeDisabled($field)
    {
        assertTrue(
            $this->findPropertyFieldByLabel($field)->hasAttribute('disabled'),
            sprintf('Expecting field "%s" to be disabled.', $field)
        );
    }

    /**
     * Same function as above but specific to the Axis field which differ
     *
     * @Then /^the variant group property "Axis" should be disabled$/
     *
     * @throws ExpectationException
     */
    public function theVariantGroupPropertyAxisShouldBeDisabled()
    {
        $node = $this->spin(function () {
            return $this->getSession()->getPage()->find('css', 'label:contains("Axis")');
        }, 'Unable to find a label containing "Axis"');

        $field = $this->spin(function () use ($node) {
            return $node->getParent()->find('css', 'input');
        }, 'Unable to find an input in the parent of the label "Axis"');

        assertTrue($field->hasAttribute('disabled'), 'Expecting field "Axis" to be disabled.');
    }

    /**
     * @param string $label
     * @param string $value
     *
     * @Given /^I fill in the variant group property "([^"]*)" with "([^"]*)"$/
     */
    public function iFillInTheVariantGroupPropertyWith($label, $value)
    {
        $this->findPropertyFieldByLabel($label)->setValue($value);
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

        assertTrue($node->hasAttribute('for'));

        return $this->spin(function () use ($node) {
            return $this->getSession()->getPage()->find('css', sprintf('#%s', $node->getAttribute('for')));
        }, sprintf('Unable to find element with id "%s"', $node->getAttribute('for')));
    }

    /**
     * @param GroupInterface $group
     */
    private function saveVariantGroup(GroupInterface $group)
    {
        $saver = $this->getService('pim_catalog.saver.group');
        $saver->save($group);
    }
}
