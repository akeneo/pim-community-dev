<?php

namespace Akeneo\Tool\Component\Connector\Step;

use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\AbstractStep;
use Akeneo\Tool\Component\Batch\Step\TrackableStepInterface;
use Akeneo\Tool\Component\Connector\Item\CharsetValidator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Validator Step for imports
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ValidatorStep extends AbstractStep implements TrackableStepInterface
{
    /** @var CharsetValidator */
    protected $charsetValidator;

    /**
     * @param string                   $name
     * @param EventDispatcherInterface $eventDispatcher
     * @param JobRepositoryInterface   $jobRepository
     * @param CharsetValidator         $charsetValidator
     */
    public function __construct(
        $name,
        EventDispatcherInterface $eventDispatcher,
        JobRepositoryInterface $jobRepository,
        CharsetValidator $charsetValidator
    ) {
        parent::__construct($name, $eventDispatcher, $jobRepository);
        $this->charsetValidator = $charsetValidator;
    }

    /**
     * {@inheritdoc}
     */
    protected function doExecute(StepExecution $stepExecution)
    {
        $stepExecution->setTotalItems(1);
        $this->charsetValidator->setStepExecution($stepExecution);
        $this->charsetValidator->validate();
        $stepExecution->incrementProcessedCount();
    }

    /**
     * @return CharsetValidator
     */
    public function getCharsetValidator()
    {
        return $this->charsetValidator;
    }

    public function isTrackable(): bool
    {
        return true;
    }
}
