<?php

declare(strict_types=1);

namespace Pim\Behat\Context\Domain\Enrich;

use Context\Spin\SpinCapableTrait;
use PHPUnit\Framework\Assert;
use Pim\Behat\Context\PimContext;

/**
 * Context responsible for handling the family variants updates steps through the UI.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyVariantConfigurationContext extends PimContext
{
    use SpinCapableTrait;

    /**
     * @Given /^I save the family variant$/
     */
    public function iSaveTheFamilyVariant()
    {
        $this->getCurrentPage()->saveFamilyVariant();
    }

    /**
     * @When /^I remove the "([^"]*)" attribute from the level (\d+)$/
     */
    public function iRemoveTheAttributeFromTheLevel(string $attributeLabel, int $level): void
    {
        if (0 === $level) {
            throw new \invalidArgumentException('Impossible to remove attributes from the common attribute list');
        }

        $attributeSetConfigurator = $this->getElementOnCurrentPage('edit family variant attribute sets');

        $attributeSet = $this->spin(function () use ($attributeSetConfigurator, $level) {
            return $attributeSetConfigurator->find(
                'css',
                sprintf('.attribute-list[data-level=%s]', $level)
            );
        }, sprintf('Unable to find the attribute list for level "%s"', $level));

        $attribute = $this->spin(function () use ($attributeSet, $attributeLabel) {
            return $attributeSet->find(
                'css',
                sprintf('.AknFamilyVariant-attribute:contains("%s")', $attributeLabel)
            );
        }, sprintf('Cannot find attribute for label "%s" and level "%s"', $attributeLabel, $level));

        $deleteAttributeButton = $attribute->find('css', '.AknIconButton--delete');
        $deleteAttributeButton->click();
    }

    /**
     * @Then /^the attribute "([^"]*)" should be on the attributes level (\d+)$/
     */
    public function theAttributeShouldBeOnTheAttributesLevel(string $attributeLabel, int $level): void
    {
        $attributeSetConfigurator = $this->getElementOnCurrentPage('edit family variant attribute sets');

        $attributeSet = $this->spin(function () use ($attributeSetConfigurator, $level) {
            return $attributeSetConfigurator->find(
                'css',
                sprintf('.attribute-list[data-level=%s]', $level)
            );
        }, sprintf('Unable to find the attribute list for level "%s"', $level));

        $attribute = $this->spin(function () use ($attributeSet, $attributeLabel) {
            return $attributeSet->find(
                'css',
                sprintf('.AknFamilyVariant-attribute:contains("%s")', $attributeLabel)
            );
        }, sprintf('Cannot find attribute for label "%s" and level "%s"', $attributeLabel, $level));

        Assert::assertNotNull($attribute);
    }

    /**
     * @Then /^I should not be able to remove the "([^"]*)" attribute from the level (\d+)$/
     */
    public function iShouldNotBeAbleToRemoveTheAttributeFromTheLevel(string $attributeLabel, int $level): void
    {
        if (0 === $level) {
            throw new \invalidArgumentException('Impossible to remove attributes from the common attribute list');
        }

        $attributeSetConfigurator = $this->getElementOnCurrentPage('edit family variant attribute sets');

        $attributeSet = $this->spin(function () use ($attributeSetConfigurator, $level) {
            return $attributeSetConfigurator->find(
                'css',
                sprintf('.attribute-list[data-level=%s]', $level)
            );
        }, sprintf('Unable to find the attribute list for level "%s"', $level));

        $attribute = $this->spin(function () use ($attributeSet, $attributeLabel) {
            return $attributeSet->find(
                'css',
                sprintf('.AknFamilyVariant-attribute:contains("%s")', $attributeLabel)
            );
        }, sprintf('Cannot find attribute for label "%s" and level "%s"', $attributeLabel, $level));

        Assert::assertNull($attribute->find('css', '.AknIconButton--delete'));
    }

    /**
     * @Then /^I move the "([^"]*)" attribute from level (\d+) to level (\d+)$/
     */
    public function iMoveAttributeToLevel(string $attributeLabel, int $fromLevel, int $toLevel): void
    {
        $attributeSetConfigurator = $this->getElementOnCurrentPage('edit family variant attribute sets');

        $attributeSetFrom = $this->spin(function () use ($attributeSetConfigurator, $fromLevel) {
            return $attributeSetConfigurator->find(
                'css',
                sprintf('.attribute-list[data-level=%s]', $fromLevel)
            );
        }, sprintf('Unable to find the attribute list for level "%s"', $fromLevel));

        $attribute = $this->spin(function () use ($attributeSetFrom, $attributeLabel) {
            return $attributeSetFrom->find(
                'css',
                sprintf('.AknFamilyVariant-attribute:contains("%s")', $attributeLabel)
            );
        }, sprintf('Cannot find attribute for label "%s" and level "%s"', $attributeLabel, $fromLevel));


        $attributeSetTo = $this->spin(function () use ($attributeSetConfigurator, $toLevel) {
            return $attributeSetConfigurator->find(
                'css',
                sprintf('.attribute-list[data-level=%s] ul', $toLevel)
            );
        }, sprintf('Unable to find the attribute list for level "%s"', $toLevel));

        $this->dragElementTo($attribute, $attributeSetTo);
    }

    /**
     * Drags an element on another one.
     * Works better than the standard dragTo.
     *
     * @param $element
     * @param $dropZone
     */
    protected function dragElementTo($element, $dropZone)
    {
        $session = $this->getSession()->getDriver()->getWebDriverSession();

        $from = $session->element('xpath', $element->getXpath());
        $to = $session->element('xpath', $dropZone->getXpath());

        $session->moveto(['element' => $from->getID()]);
        $session->buttondown('');
        $session->moveto(['element' => $to->getID()]);
        $session->buttonup('');
    }
}
