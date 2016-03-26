<?php
namespace Pim\Bundle\InstallerBundle\SimpleCommand;

class SimpleCommand implements SimpleCommandInterface
{
    /** @var string */
    protected $command;
    /** @var array */
    protected $params;

    /**
     * SimpleCommand constructor.
     * @param string $command
     * @param array $params
     */
    public function __construct($command, array $params = [])
    {
        $this->command = $command;
        $this->params = $params;
    }

    /**
     * Creates a new command
     *
     * @param string $command
     * @param array $params
     *
     * @return SimpleCommand
     */
    public static function create($command, array $params = [])
    {
        return new self($command, $params);
    }

    /**
     * Creates multiple commands
     *
     * Expected input is an array of arrays representing commands,
     * with the key being the command and the value being the parameters
     *
     * Example input: ["fos:js-routing:dump" => ["--target" => "web/js/routes.js"]]
     *
     * @param array $commandDefinitions
     *
     * @return array - Array of SimpleCommandInterface
     */
    public static function createAll(array $commandDefinitions)
    {
        $commands = [];
        foreach($commandDefinitions as $command => $params)
        {
            $commands[] = new self($command, $params);
        }

        return $commands;
    }

    /**
     * @inheritdoc
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * @inheritdoc
     */
    public function setCommand($command)
    {
        $this->command = $command;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @inheritdoc
     */
    public function setParams(array $params)
    {
        $this->params = $params;
        return $this;
    }
}