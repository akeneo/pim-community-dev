<?php

namespace Context;

use Behat\Gherkin\Node\TableNode;
use Pim\Behat\Context\PimContext;
use Pim\Bundle\CatalogBundle\Command\GetProductCommand;
use Pim\Bundle\CatalogBundle\Command\QueryProductCommand;
use Pim\Bundle\CatalogBundle\Command\UpdateProductCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Process\PhpExecutableFinder;

/**
 * Context for commands
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CommandContext extends PimContext
{
    /**
     * @Given /^I launched the completeness calculator$/
     */
    public function iLaunchedTheCompletenessCalculator()
    {
        $commandLauncher = $this->getService('pim_catalog.command_launcher');
        $commandLauncher->executeForeground('pim:completeness:calculate');
    }

    /**
     * @param TableNode $filters
     *
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
     * @Then /^I should get the following products after apply the following updater to it:$/
     *
     * @param TableNode $updates
     *
     * @throws \Exception
     */
    public function iShouldGetTheFollowingProductsAfterApplyTheFollowingUpdaterToIt(TableNode $updates)
    {
        $application = $this->getApplicationsForUpdaterProduct();

        $updateCommand = $application->find('pim:product:update');
        $updateCommand->setContainer($this->getMainContext()->getContainer());
        $updateCommandTester = new CommandTester($updateCommand);

        $getCommand = $application->find('pim:product:get');
        $getCommand->setContainer($this->getMainContext()->getContainer());
        $getCommandTester = new CommandTester($getCommand);

        foreach ($updates->getHash() as $update) {
            $username = isset($update['username']) ? $update['username'] : null;

            $updateCommandTester->execute(
                [
                    'command'      => $updateCommand->getName(),
                    'identifier'   => $update['product'],
                    'json_updates' => $this->sanitizeProductActions($update['actions']),
                    'username'     => $username
                ]
            );

            $expected = json_decode($update['result'], true);
            if (isset($expected['product'])) {
                $getCommandTester->execute(
                    [
                        'command'    => $getCommand->getName(),
                        'identifier' => $expected['product']
                    ]
                );
                unset($expected['product']);
            } else {
                $getCommandTester->execute(
                    [
                        'command'    => $getCommand->getName(),
                        'identifier' => $update['product']
                    ]
                );
            }

            $actual = json_decode($getCommandTester->getDisplay(), true);

            if (null === $actual) {
                throw new \Exception(sprintf(
                    'An error occured during the execution of the update command : %s',
                    $getCommandTester->getDisplay()
                ));
            }

            if (null === $expected) {
                throw new \Exception(sprintf(
                    'Looks like the expected result is not valid json : %s',
                    $update['result']
                ));
            }
            $diff = $this->arrayIntersect($actual, $expected);

            assertEquals(
                $expected,
                $diff
            );
        }
    }

    /**
     * @When /^I launch the purge versions command for entity "([^"]*)"$/
     * @When /^I launch the purge versions command"$/
     *
     * @param string $entityName
     */
    public function iLaunchThePurgeCommandForEntityOlderThanDays($entityName = '')
    {
        $commandLauncher = $this->getService('pim_catalog.command_launcher');
        $commandLauncher->executeForeground(
            sprintf('pim:versioning:purge %s --more-than-days 0 --force', $entityName)
        );
    }

    /**
     * @param string $rawActions
     *
     * @return string
     */
    protected function sanitizeProductActions($rawActions)
    {
        $actions = json_decode($rawActions);

        foreach ($actions as $key => $action) {
            if (isset($action->data->filePath)) {
                $action->data->filePath = self::replacePlaceholders($action->data->filePath);
            }
        }

        return json_encode($actions);
    }

    /**
     * @return Application
     */
    protected function getApplicationsForUpdaterProduct()
    {
        $application = new Application();
        $application->add(new UpdateProductCommand());
        $application->add(new GetProductCommand());

        return $application;
    }

    /**
     * Recursive intersect for nested associative array
     *
     * @param array $array1
     * @param array $array2
     *
     * @return array
     */
    protected function arrayIntersect($array1, $array2)
    {
        $isAssoc = array_keys($array1) !== range(0, count($array1) - 1);
        foreach ($array1 as $key => $value) {
            if ($isAssoc) {
                if (!array_key_exists($key, $array2)) {
                    unset($array1[$key]);
                } else {
                    if (is_array($value)) {
                        $array1[$key] = $this->arrayIntersect($value, $array2[$key]);
                    }
                }
            } else {
                if (is_array($value)) {
                    $array1[$key] = $this->arrayIntersect($value, $array2[$key]);
                }
            }
        }

        return $array1;
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
    protected function getFixturesContext()
    {
        return $this->getMainContext()->getSubcontext('fixtures');
    }

    /**
     * Runs app/console $command in the test environment
     *
     * @When /^I run '([^\']*)'( in background)?$/
     *
     * @param string      $command
     * @param string|null $command
     */
    public function iRun($command, $background)
    {
        $commandLauncher = $this->getService('pim_catalog.command_launcher');

        if (null === $background) {
            $commandLauncher->executeForeground($this->replacePlaceholders($command));
        } else {
            $commandLauncher->executeBackground($this->replacePlaceholders($command));
        }
    }
}
