<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Bundle\FrameworkBundle\AclCache;

interface FetchSamplePercentage
{
    /**
     * @return int percentage of requests to sample for in memory Acl cache provider
     */
    public function fetch(): int;
}
