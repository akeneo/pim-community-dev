<?php

namespace Oro\Bundle\DataGridBundle\Extension\Action\Actions;

use Oro\Bundle\DataGridBundle\Extension\Action\ActionConfiguration;

abstract class AbstractAction implements ActionInterface
{
    /** @var ActionConfiguration */
    protected $options;

    /** @var array */
    protected $requiredOptions = [];

    public function __construct()
    {
        // empty configuration by default
        $this->options = ActionConfiguration::create([]);
    }

    /**
     * {@inheritDoc}
     */
    public function getAclResource(): ?string
    {
        return $this->options->offsetGetOr(self::ACL_KEY);
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return $this->options->getName();
    }

    /**
     * {@inheritDoc}
     */
    public function getOptions(): \Oro\Bundle\DataGridBundle\Extension\Action\ActionConfiguration
    {
        return $this->options;
    }

    /**
     * {@inheritDoc}
     */
    public function setOptions(ActionConfiguration $options): ActionInterface
    {
        $this->options = $options;
        $this->assertHasRequiredOptions();

        return $this;
    }

    /**
     * Accert required options array
     */
    protected function assertHasRequiredOptions()
    {
        foreach ($this->requiredOptions as $optionName) {
            $this->assertHasRequiredOption($optionName);
        }
    }

    /**
     * Assert required single option
     *
     * @param string $optionName
     *
     * @throws \LogicException
     */
    protected function assertHasRequiredOption(string $optionName)
    {
        if (!isset($this->options[$optionName])) {
            throw new \LogicException(
                'There is no option "' . $optionName . '" for action "' . $this->getName() . '".'
            );
        }
    }
}
