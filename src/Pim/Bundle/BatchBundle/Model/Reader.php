<?php

namespace Pim\Bundle\BatchBundle\Model;

/**
 * Base reader class that must extends other readers
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class Reader extends AbstractConfigurableStepElement
{
    abstract public function read();
}
