<?php

namespace Oro\Bundle\SecurityBundle\EventListener;

use Oro\Bundle\DataGridBundle\Event\GetResultsBefore;
use Oro\Bundle\SecurityBundle\ORM\Walker\ACLHelper;

class DataGridListener
{
    /**
     * @var ACLHelper
     */
    protected $aclHelper;

    public function __construct(ACLHelper $aclHelper)
    {
        $this->aclHelper = $aclHelper;
    }

    public function applyAclToQuery(GetResultsBefore $event)
    {
        $query = $event->getQuery();
        $this->aclHelper->apply($query);
    }
} 