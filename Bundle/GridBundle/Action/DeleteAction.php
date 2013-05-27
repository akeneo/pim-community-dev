<?php

namespace Oro\Bundle\GridBundle\Action;

class DeleteAction extends AbstractAction
{
    /**
     * @var string
     */
    protected $type = self::TYPE_DELETE;

    /**
     * @var array
     */
    protected $requiredOptions = array('link');
}
