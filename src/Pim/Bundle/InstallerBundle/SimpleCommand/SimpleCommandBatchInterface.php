<?php
namespace Pim\Bundle\InstallerBundle\SimpleCommand;

use Doctrine\Common\Collections\ArrayCollection;

interface SimpleCommandBatchInterface
{
    public function __construct(array $commands);

    /**
     * TODO: Improve this, so that commands without params do not have to include [] as a value
     *
     * Creates a new SimpleCommandBatch
     *
     * Expected input is an array of arrays representing commands,
     * with the key being the command and the value being the parameters
     *
     * Example input: ["fos:js-routing:dump" => ["--target" => "web/js/routes.js"]]
     *
     * @param array $commandDefinitions
     *
     * @return ArrayCollection - Array of SimpleCommandInterface
     */
    public static function create(array $commandDefinitions);

    /**
     * Gets the commands
     *
     * @return ArrayCollection
     */
    public function getCommands();

    /**
     * Adds a new command to the batch
     *
     * @param SimpleCommandInterface $command
     *
     * @return $this
     */
    public function addCommand(SimpleCommandInterface $command);

    /**
     * Removes a command from the batch
     *
     * @param SimpleCommandInterface $command
     *
     * @return $this
     */
    public function removeCommand(SimpleCommandInterface $command);

}