<?php
namespace Pim\Bundle\InstallerBundle\Event;

use Pim\Bundle\InstallerBundle\SimpleCommand\SimpleCommandBatch;

class CommandBatchEvent extends InstallEvent
{
    /** @var SimpleCommandBatch */
    protected $commandBatch;

    /**
     * SimpleCommandBatchEvent constructor.
     * @param SimpleCommandBatch $commandBatch
     * @param array $arguments
     */
    public function __construct(SimpleCommandBatch $commandBatch, array $arguments = [])
    {
        $this->commandBatch = $commandBatch;
        parent::__construct($commandBatch, $arguments);
    }

    /**
     * Gets the command batch
     *
     * @return SimpleCommandBatch
     */
    public function getCommandBatch()
    {
        return $this->commandBatch;
    }

    /**
     * Sets the command batch
     *
     * @param SimpleCommandBatch $commandBatch
     *
     * @return $this
     */
    public function setCommandBatch($commandBatch)
    {
        $this->commandBatch = $commandBatch;
        return $this;
    }


}