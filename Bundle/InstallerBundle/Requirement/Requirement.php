<?php

namespace Oro\Bundle\InstallerBundle\Requirement;

class Requirement
{
    /**
     * @var string
     */
    protected $label;

    /**
     * @var mixed
     */
    protected $fulfilled;

    /**
     * @var mixed
     */
    protected $expected;

    /**
     * @var mixed
     */
    protected $actual;

    /**
     * @var string
     */
    protected $help;

    /**
     * @var bool
     */
    protected $required;

    /**
     *
     * @param string $label     Requirement label
     * @param bool   $fulfilled Is requirement fullfiled
     * @param mixed  $expected  Expected requirement value
     * @param mixed  $actual    Actual requirement value
     * @param bool   $required  Is this requirement required to continue
     * @param string $help      Some help info
     */
    public function __construct($label, $fulfilled, $expected, $actual, $required = true, $help = null)
    {
        $this->label     = $label;
        $this->fulfilled = (bool) $fulfilled;
        $this->expected  = $expected;
        $this->actual    = $actual;
        $this->required  = (bool) $required;
        $this->help      = $help;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return bool
     */
    public function isFulfilled()
    {
        return $this->fulfilled;
    }

    /**
     * @return mixed
     */
    public function getExpected()
    {
        return $this->expected;
    }

    /**
     * @return mixed
     */
    public function getActual()
    {
        return $this->actual;
    }

    /**
     * @return bool
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * @return string
     */
    public function getHelp()
    {
        return $this->help;
    }
}
