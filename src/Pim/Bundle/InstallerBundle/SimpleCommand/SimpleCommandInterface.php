<?php
namespace Pim\Bundle\InstallerBundle\SimpleCommand;

interface SimpleCommandInterface
{
    /**
     * Gets the command
     *
     * @return string
     */
    public function getCommand();

    /**
     * Sets the command
     *
     * @param string$command
     *
     * @return $this
     */
    public function setCommand($command);

    /**
     * Gets the params
     *
     * @return array
     */
    public function getParams();

    /**
     * Sets the params
     *
     * @param array $params
     *
     * @return $this
     */
    public function setParams(array $params);
}