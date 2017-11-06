<?php

declare(strict_types=1);

namespace Pim\Behat\Context\Domain\Enrich;

use Context\Spin\SpinCapableTrait;
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
     * @When /^I remove the "([^"]*)" attribute from the level (\d+)$/
     */
    public function iRemoveTheAttributeFromTheLevel(string $attributeLabel, int $level): void
    {
        if (0 >= $level) {
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
     * @Then /^the attribute "([^"]*)" should be on the common attributes level$/
     */
    public function theAttributeShouldBeOnTheCommonAttributesLevel(string $attributeLabel): void
    {
        $attributeSetConfigurator = $this->getElementOnCurrentPage('edit family variant attribute sets');

        $attributeSet = $this->spin(function () use ($attributeSetConfigurator) {
            return $attributeSetConfigurator->find(
                'css',
                sprintf('.attribute-list[data-level=0]')
            );
        }, sprintf('Unable to find the attribute list for level "0"'));

        $attribute = $this->spin(function () use ($attributeSet, $attributeLabel) {
            return $attributeSet->find(
                'css',
                sprintf('.AknFamilyVariant-attribute:contains("%s")', $attributeLabel)
            );
        }, sprintf('Cannot find attribute for label "%s" and level "0"', $attributeLabel));

        assertNotNull($attribute);
    }
}
