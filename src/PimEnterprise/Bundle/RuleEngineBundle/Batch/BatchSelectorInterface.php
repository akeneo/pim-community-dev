<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\RuleEngineBundle\Batch;

use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Engine\SelectorInterface;

/**
 * Selects rule subjects via a batch.
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
interface BatchSelectorInterface extends SelectorInterface, StepExecutionAwareInterface
{
}
