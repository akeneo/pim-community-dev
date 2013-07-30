<?php

namespace Oro\Bundle\WorkflowBundle\Model\PostAction;

use Psr\Log\LoggerInterface;

class ListExecutor implements PostActionInterface
{
    const ALIAS = 'list';

    /**
     * @var array
     */
    protected $postActions = array();

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var string
     */
    protected $logLevel;

    public function __construct(LoggerInterface $logger = null, $logLevel = 'ALERT')
    {
        $this->logger = $logger;
        $this->logLevel = $logLevel;
    }

    /**
     * @param PostActionInterface $postAction
     * @param bool $breakOnFailure
     * @return ListExecutor
     */
    public function addPostAction(PostActionInterface $postAction, $breakOnFailure = true)
    {
        $this->postActions[] = array(
            'instance' => $postAction,
            'breakOnFailure' => $breakOnFailure
        );

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function execute($context)
    {
        foreach ($this->postActions as $postActionConfig) {
            try {
                /** @var PostActionInterface $postAction */
                $postAction = $postActionConfig['instance'];
                $postAction->execute($context);
            } catch (\Exception $e) {
                if ($postActionConfig['breakOnFailure']) {
                    throw $e;
                } elseif (null !== $this->logger) {
                    $this->logger->log($this->logLevel, $e->getMessage());
                }
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function initialize(array $options)
    {
        return $this;
    }
}
