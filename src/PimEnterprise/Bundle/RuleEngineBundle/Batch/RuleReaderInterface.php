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

/**
 * Get a rule
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
interface RuleReaderInterface
{
    /**
     * Return a rule
     *
     * @return RuleInterface
     */
    public function read();
}
