<?php
namespace Pim\Bundle\InstallerBundle\SimpleCommand;

use Pim\Bundle\InstallerBundle\CommandExecutor;

class SimpleCommandExecutor extends CommandExecutor
{
    /**
     * Run single command
     *
     * @param SimpleCommandInterface $command
     *
     * @return $this
     */
    public function run(SimpleCommandInterface $command)
    {
        return parent::runCommand($command->getCommand(), $command->getParams());
    }

    /**
     * Runs multiple commands
     *
     * @param array $commands
     *
     * @return $this
     */
    public function runAll(array $commands)
    {
        foreach($commands as $command)
        {
            $this->run($command);
        }
        return $this;
    }

    /**
     * Runs a command batch
     *
     * @param SimpleCommandBatchInterface $commandBatch
     *
     * @return $this
     */
    public function runBatch(SimpleCommandBatchInterface $commandBatch)
    {
        return $this->runAll($commandBatch->getCommands()->toArray());
    }
}
