<?php

namespace Pim\Bundle\BatchBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Connector class that must extends any connector in order to register the jobs
 *
 * @see Pim\Bundle\BatchBundle\DependencyInjection\Compiler\RegisterJobsPass
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Connector extends Bundle
{
}
