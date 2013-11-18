<?php

namespace Oro\Bundle\WorkflowBundle\Model\Action;

use Psr\Log\LoggerInterface;

class TreeExecutor extends AbstractAction
{
    const ALIAS = 'tree';

    /**
     * @var array
     */
    protected $actions = array();

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var string
     */
    protected $logLevel;

    /**
     * @param LoggerInterface $logger
     * @param string $logLevel
     */
    public function __construct(LoggerInterface $logger = null, $logLevel = 'ALERT')
    {
        $this->logger = $logger;
        $this->logLevel = $logLevel;
    }

    /**
     * @param ActionInterface $action
     * @param bool $breakOnFailure
     * @return TreeExecutor
     */
    public function addAction(ActionInterface $action, $breakOnFailure = true)
    {
        $this->actions[] = array(
            'instance' => $action,
            'breakOnFailure' => $breakOnFailure
        );

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    protected function executeAction($context)
    {
        foreach ($this->actions as $actionConfig) {
            try {
                /** @var ActionInterface $action */
                $action = $actionConfig['instance'];
                $action->execute($context);
            } catch (\Exception $e) {
                if ($actionConfig['breakOnFailure']) {
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
