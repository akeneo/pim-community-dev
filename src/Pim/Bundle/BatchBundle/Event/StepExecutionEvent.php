<?php

namespace Pim\Bundle\BatchBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Pim\Bundle\BatchBundle\Entity\StepExecution;

/**
 * Event triggered during stepExecution execution
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class StepExecutionEvent extends Event implements EventInterface
{
    protected $stepExecution;

    public function __construct(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    public function getStepExecution()
    {
        return $this->stepExecution;
    }
}
