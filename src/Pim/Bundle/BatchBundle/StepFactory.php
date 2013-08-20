<?php

namespace Pim\Bundle\BatchBundle;

use Pim\Bundle\BatchBundle\Model\Step;

/**
 * Step instance factory
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class StepFactory
{
    public function createStep($name, $reader, $processor, $writer)
    {
        $step = new Step();
        $step->setName($name);
        $step->setReader($reader);
        $step->setProcessor($processor);
        $step->setWriter($writer);

        return $step;
    }
}
