<?php

namespace Oro\Bundle\DataGridBundle\Extension\MassAction;

class MassActionResponse implements MassActionResponseInterface
{
    /** @var boolean */
    protected $successful;

    /**  @var string */
    protected $message;

    /** @var array */
    protected $options = [];

    /**
     * @param boolean $successful
     * @param string  $message
     * @param array   $options
     */
    public function __construct(bool $successful, string $message, array $options = [])
    {
        $this->successful = $successful;
        $this->message = $message;
        $this->options = $options;
    }

    /**
     * {@inheritDoc}
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * {@inheritDoc}
     */
    public function getOption(string $name)
    {
        return isset($this->options[$name]) ? $this->options[$name] : null;
    }

    public function isSuccessful(): bool
    {
        return $this->successful;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
