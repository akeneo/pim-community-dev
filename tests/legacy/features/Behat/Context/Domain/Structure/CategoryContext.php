<?php

declare(strict_types=1);

namespace Pim\Behat\Context\Domain\Structure;

use Behat\Mink\Element\NodeElement;
use Context\Spin\SpinCapableTrait;
use Pim\Behat\Context\PimContext;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CategoryContext extends PimContext
{
    use SpinCapableTrait;

    /**
     * @When I follow the :categoryLabel category
     */
    public function iFollowTheCategory(string $categoryLabel)
    {
        /** @var NodeElement $categoryTree */
        $categoryTree = $this->spin(function () use ($categoryLabel) {
            return $this->getCurrentPage()->find('named', array('content', $categoryLabel));

            //return $this->getSession()->getPage()->find('xpath', sprintf('//td[text()="%s"]', $categoryTreeLabel));
        }, sprintf('The "%s" category was not found', $categoryLabel));
        $categoryTree->click();
    }

    /**
     * @When I hover over the category ":categoryLabel"
     */
    public function iHoverOverTheCategory(string $categoryLabel)
    {
        /** @var NodeElement $categoryTree */
        $categoryTree = $this->spin(function () use ($categoryLabel) {
            return $this->getCurrentPage()->find('named', array('content', $categoryLabel));
        }, sprintf('The "%s" category was not found', $categoryLabel));

        $categoryTree->mouseOver();
    }
}
