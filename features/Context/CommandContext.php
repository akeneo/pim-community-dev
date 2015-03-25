<?php

namespace Context;

use Akeneo\Bundle\BatchBundle\Command\BatchCommand;
use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\RawMinkContext;
use Pim\Bundle\CatalogBundle\Command\GetProductCommand;
use Pim\Bundle\CatalogBundle\Command\QueryProductCommand;
use Pim\Bundle\CatalogBundle\Command\UpdateProductCommand;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\EnrichBundle\MassEditAction\Operation\BatchableOperationInterface;
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
     * @Then /^I should get the following products after apply the following updater to it:$/
     */
    public function iShouldGetTheFollowingProductsAfterApplyTheFollowingUpdaterToIt(TableNode $updates)
    {
        $application = new Application();
        $application->add(new UpdateProductCommand());
        $application->add(new GetProductCommand());

        $updateCommand = $application->find('pim:product:update');
        $updateCommand->setContainer($this->getMainContext()->getContainer());
        $updateCommandTester = new CommandTester($updateCommand);

        $getCommand = $application->find('pim:product:get');
        $getCommand->setContainer($this->getMainContext()->getContainer());
        $getCommandTester = new CommandTester($getCommand);

        foreach ($updates->getHash() as $update) {
            $updateCommandTester->execute(
                [
                    'command'      => $updateCommand->getName(),
                    'identifier'   => $update['product'],
                    'json_updates' => $update['actions']
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
     * @Given /^I apply the following mass-edit operation with the given configuration:$/
     */
    public function iApplyTheFollowingMassEditOperationWithTheGivenConfiguration(TableNode $updates)
    {
        $application = new Application();
        $application->add(new BatchCommand());

        $batchCommand = $application->find('akeneo:batch:job');
        $batchCommand->setContainer($this->getMainContext()->getContainer());
        $batchCommandTester = new CommandTester($batchCommand);

        $operationRegistry = $this->getContainer()->get('pim_enrich.mass_edit_action.operation.registry');

        foreach ($updates->getHash() as $update) {

            $operation = $operationRegistry->get($update['operation']);

            if (! $operation instanceof BatchableOperationInterface) {
                throw new \Exception(sprintf(
                    'Operation with alias %s must implement the BatchableOperationInterface to be tested this way',
                    $operation->getAlias()
                ));
            }

            $filters = json_decode($update['filters'], true);
            $actions = json_decode($update['actions'], true);

            $jobArguments = [
                'command'  => $batchCommand->getName(),
                'code'     => $operation->getBatchJobCode(),
                '--config' => json_encode([
                    'filters' => $filters,
                    'actions' => $actions
                ]),
                '--no-log' => true
            ];

            $batchCommandTester->execute($jobArguments);
        }
    }

    /**
     * @Then /^I should get the following products after apply the following mass-edit operation to it:$/
     */
    public function iShouldGetTheFollowingProductsAfterApplyTheFollowingMassEditOperationToIt(TableNode $updates)
    {
        $application = new Application();
        $application->add(new BatchCommand());
        $application->add(new GetProductCommand());

        $batchCommand = $application->find('akeneo:batch:job');
        $batchCommand->setContainer($this->getMainContext()->getContainer());
        $batchCommandTester = new CommandTester($batchCommand);

        $getCommand = $application->find('pim:product:get');
        $getCommand->setContainer($this->getMainContext()->getContainer());
        $getCommandTester = new CommandTester($getCommand);

        $operationRegistry = $this->getContainer()->get('pim_enrich.mass_edit_action.operation.registry');
        $pqbFactory = $this->getContainer()->get('pim_catalog.query.product_query_builder_factory');

        foreach ($updates->getHash() as $update) {

            $operation = $operationRegistry->get($update['operation']);
            $productQueryBuilder = $pqbFactory->create();

            if (! $operation instanceof BatchableOperationInterface) {
                throw new \Exception(sprintf(
                    'Operation with alias %s must implement the BatchableOperationInterface to be tested this way',
                    $operation->getAlias()
                ));
            }

            $filters = json_decode($update['filters'], true);
            $actions = json_decode($update['actions'], true);

            foreach ($filters as $filter) {
                $productQueryBuilder->addFilter($filter['field'], $filter['operator'], $filter['value']);
            }

            $cursor = $productQueryBuilder->execute();

            $jobArguments = [
                'command'  => $batchCommand->getName(),
                'code'     => $operation->getBatchJobCode(),
                '--config' => json_encode([
                    'filters' => $filters,
                    'actions' => $actions
                ]),
                '--no-log' => true
            ];

            $batchCommandTester->execute($jobArguments);

            foreach ($cursor as $product) {
                $getCommandTester->execute(
                    [
                        'command'    => $getCommand->getName(),
                        'identifier' => (string) $product->getIdentifier()
                    ]
                );

                $expected = json_decode($update['result'], true);
                $actual   = json_decode($getCommandTester->getDisplay(), true);

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
}
