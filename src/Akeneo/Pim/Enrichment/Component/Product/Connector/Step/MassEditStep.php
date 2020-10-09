<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Step;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Item\MassEdit\TemporaryFileCleaner;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\AbstractStep;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * BatchBundle Step for standard mass edit products
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MassEditStep extends AbstractStep
{
    /** @var StepExecutionAwareInterface */
    protected $cleaner;

    /**
     * @param string                   $name
     * @param EventDispatcherInterface $eventDispatcher
     * @param JobRepositoryInterface   $jobRepository
     * @param TemporaryFileCleaner     $cleaner
     */
    public function __construct(
        $name,
        EventDispatcherInterface $eventDispatcher,
        JobRepositoryInterface $jobRepository,
        TemporaryFileCleaner $cleaner
    ) {
        parent::__construct($name, $eventDispatcher, $jobRepository);
        $this->cleaner = $cleaner;
    }

    /**
     * {@inheritdoc}
     */
    protected function doExecute(StepExecution $stepExecution)
    {
        $this->cleaner->setStepExecution($stepExecution);
        $this->cleaner->execute();
    }

    /**
     * @return TemporaryFileCleaner
     */
    public function getCleaner()
    {
        return $this->cleaner;
    }

    /**
     * @param StepExecutionAwareInterface $cleaner
     *
     * @return MassEditStep
     */
    public function setCleaner(StepExecutionAwareInterface $cleaner)
    {
        $this->cleaner = $cleaner;

        return $this;
    }
}
