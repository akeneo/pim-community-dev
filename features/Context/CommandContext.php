<?php

namespace Context;

use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\RawMinkContext;
use Pim\Bundle\CatalogBundle\Command\QueryProductCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Context for commands
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CommandContext extends RawMinkContext
{
    /**
     * @Given /^I launched the completeness calculator$/
     */
    public function iLaunchedTheCompletenessCalculator()
    {
        $this->getFixturesContext()->clearUOW();
        $this
            ->getContainer()
            ->get('pim_catalog.manager.completeness')
            ->generateMissing();
    }

    /**
     * @Then /^I should get the following results for the given filters:$/
     */
    public function iShouldGetTheFollowingResultsForTheGivenFilters(TableNode $filters)
    {
        $application = new Application();
        $application->add(new QueryProductCommand());

        $command = $application->find('pim:product:query');
        $command->setContainer($this->getMainContext()->getContainer());
        $commandTester = new CommandTester($command);

        foreach ($filters->getHash() as $filter) {
            $commandTester->execute(
                ['command' => $command->getName(), '--json-output' => true, 'json_filters' => $filter['filter']]
            );

            $expected = json_decode($filter['result']);
            $actual   = json_decode($commandTester->getDisplay());
            sort($expected);
            sort($actual);
            assertEquals($expected, $actual);
        }
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected function getContainer()
    {
        return $this->getMainContext()->getContainer();
    }

    /**
     * @return FixturesContext
     */
    private function getFixturesContext()
    {
        return $this->getMainContext()->getSubcontext('fixtures');
    }
}
