<?php

namespace Oro\Bundle\QueryDesignerBundle\QueryDesigner;

use Oro\Bundle\QueryDesignerBundle\Provider\SystemAwareResolver;

class Manager
{
    /** @var array */
    protected $configuration = [];

    /** @var bool */
    protected $processed = false;

    public function __construct(
        array $configuration,
        SystemAwareResolver $resolver
    ) {
        $this->configuration = $configuration;
        $this->resolver         = $resolver;
    }

    /**
     * Returns prepared config for query designer
     *
     * @return Configuration
     */
    public function getConfiguration()
    {
        if (!$this->processed) {
            $this->resolver->resolve($this->configuration);
            $this->processed = true;
        }

        return Configuration::create($this->configuration);
    }
}
