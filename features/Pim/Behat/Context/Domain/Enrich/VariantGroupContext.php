<?php

namespace Pim\Behat\Context\Domain\Enrich;

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\ExpectationException;
use Pim\Behat\Context\PimContext;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;

class VariantGroupContext extends PimContext
{
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

        $variantGroup = $this->getFixturesContext()->loadFixture('variant_groups', $data);
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
        $this->getNavigationContext()->openPage(sprintf('%s edit', $page), ['id' => $entity->getId()]);
    }

    /**
     * @param GroupInterface $group
     *
     * @Given /^I should be on the ("([^"]*)" variant group) page$/
     */
    public function iShouldBeOnTheVariantGroupPage(GroupInterface $group)
    {
        $expectedAddress = $this->getPage('VariantGroup edit')->getUrl(['id' => $group->getId()]);
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
     * @param GroupInterface $group
     */
    private function saveVariantGroup(GroupInterface $group)
    {
        $saver = $this->getService('pim_catalog.saver.group');
        $saver->save($group);
    }
}
