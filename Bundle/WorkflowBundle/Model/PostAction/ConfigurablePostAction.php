<?php

namespace Oro\Bundle\WorkflowBundle\Model\PostAction;

class ConfigurablePostAction implements PostActionInterface
{
    /**
     * @var PostActionAssembler
     */
    protected $assembler;

    /**
     * @var PostActionInterface
     */
    protected $postAction;

    /**
     * @var array
     */
    protected $configuration = array();

    /**
     * @param PostActionAssembler $assembler
     */
    public function __construct(PostActionAssembler $assembler)
    {
        $this->assembler = $assembler;
    }

    /**
     * @param array $configuration
     */
    public function initialize(array $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * {@inheritDoc}
     */
    public function execute($context)
    {
        if (!$this->postAction) {
            $this->postAction = $this->assembler->assemble($this->configuration);
        }

        $this->postAction->execute($context);
    }
}
