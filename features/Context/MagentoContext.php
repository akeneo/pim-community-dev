<?php

namespace Context;

use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Behat\Context\Step;
use Behat\Gherkin\Node\TableNode;
use SensioLabs\Behat\PageObjectExtension\Context\PageObjectAwareInterface;
use SensioLabs\Behat\PageObjectExtension\Context\PageFactory;
use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Oro\Bundle\UserBundle\Entity\Role;
use Behat\Mink\Exception\ElementNotFoundException;

/**
 * Context for Magento connector
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagentoContext extends RawMinkContext implements PageObjectAwareInterface
{
    /** @var PageFactory $pageFactory */
    protected $pageFactory = null;

    /**
     * @param PageFactory $pageFactory
     */
    public function setPageFactory(PageFactory $pageFactory)
    {
        $this->pageFactory = $pageFactory;
    }

    /**
     * @Given /^I fill in the "([^"]*)" mapping:$/
     */
    public function iFillTheMapping($arg1, TableNode $table)
    {
        $page = $this->getSession()->getPage();

        $mappingAreas = $page->findAll('css', 'div.mapping-field');
        $mappingElements = [];
        foreach ($mappingAreas as $mappingElement) {
            if (null !== $mappingElement->find('css', 'label[for*=' . $arg1 .']')) {
                $sourceElement = $mappingElement->find(
                    'css',
                    'div.mapping-source ul.select2-choices,
                        div.mapping-source a.select2-choice'
                );

                $targetElement = $mappingElement->find(
                    'css',
                    'div.mapping-target ul.select2-choices,
                        div.mapping-target a.select2-choice'
                );
                break;
            }
        }

        if (isset($sourceElement) && isset($targetElement)) {
            $mapping = [];
            foreach ($table->getRows() as $row) {
                $mapping[] = ['source' => $row[0], 'target' => $row[1]];
            }

            // TODO : Add a foreach($mapping) for multiple rows mapping cases
            $sourceElement->click();
            $foundSource = false;
            $sourceOptions = $page->findAll('css', 'div.select2-result-label');
            foreach ($sourceOptions as $sourceOption) {
                if (false !== strpos($sourceOption->getHtml(), $mapping[0]['source'])) {
                    $sourceOption->click();
                    $foundSource = true;
                    break;
                }
            }
            if (false === $foundSource) {
                throw new ElementNotFoundException(
                    $this->getSession(),
                    $mapping[0]['source'],
                    'css'
                );
            }

            $targetElement->click();
            $foundTarget = false;
            $targetOptions = $page->findAll('css', 'div.select2-result-label');
            foreach ($targetOptions as $targetOption) {
                if (false !== strpos($targetOption->getHtml(), $mapping[0]['target'])) {
                    $targetOption->click();
                    $foundTarget = true;
                    break;
                }
            }
            if (false === $foundTarget) {
                throw new ElementNotFoundException(
                    $this->getSession(),
                    $arg1 . ' mapping',
                    'css',
                    $mapping[0]['target']
                );
            }
        } else {
            throw new ElementNotFoundException(
                $this->getSession(),
                $arg1 . ' mapping',
                'css',
                'label[for*=' . $arg1 .']'
            );
        }
    }

    /**
     * @param string $button
     * @param int    $timeToWait in seconds
     *
     * @Given /^I press the "([^"]*)" button and I wait "([^"]*)"s$/
     */
    public function iPressTheButtonAndIWait($button, $timeToWait)
    {
        $this->getSession()->getPage()->pressButton($button);
        $this->getMainContext()->wait($timeToWait * 1000);
    }
}
