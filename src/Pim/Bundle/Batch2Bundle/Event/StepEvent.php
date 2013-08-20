<?php

namespace Pim\Bundle\Batch2Bundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Pim\Bundle\Batch2Bundle\Model\Step;

/**
 * 
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class StepEvent extends Event implements EventInterface
{
    protected $step;

    public function __construct(Step $step)
    {
        $this->step = $step;
    }

    public function getStep()
    {
        return $this->step;
    }
}
