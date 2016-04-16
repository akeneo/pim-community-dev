<?php
namespace Pim\Bundle\InstallerBundle\SimpleCommand;

use Doctrine\Common\Collections\ArrayCollection;

class SimpleCommandBatch implements SimpleCommandBatchInterface
{
    /** @var ArrayCollection */
    protected $commands;
    public function __construct(array $commands)
    {
        $this->commands = new ArrayCollection($commands);
    }

    /**
     * @inheritdoc
     */
    public static function create(array $commandDefinitions)
    {
        $commands = SimpleCommand::createAll($commandDefinitions);
        return new self($commands);
    }

    /**
     * @inheritdoc
     */
    public function getCommands()
    {
        return $this->commands;
    }

    /**
     * @inheritdoc
     */
    public function addCommand(SimpleCommandInterface $command)
    {
        $this->commands->add($command);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeCommand(SimpleCommandInterface $command)
    {
        $this->commands->removeElement($command);
    }

}