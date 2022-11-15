<?php

namespace Akeneo\Platform\Bundle\FrameworkBundle\AclCache;

interface FetchSamplePercentage
{
    /**
     * @return int percentage of requests to sample for in memory Acl cache provider
     */
    public function fetch(): int;
}