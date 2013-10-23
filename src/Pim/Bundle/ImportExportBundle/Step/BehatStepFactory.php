<?php

namespace Pim\Bundle\ImportExportBundle\Step;

use Oro\Bundle\BatchBundle\Step\StepFactory;

/**
 * Overrides StepFactory to use a low batchSize for importations
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BehatStepFactory extends StepFactory
{
    public function createStep($title, $reader, $processor, $writer)
    {
        $step = parent::createStep($title, $reader, $processor, $writer);
        $step->setBatchSize(5);

        return $step;
    }
}
