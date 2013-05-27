<?php

namespace Oro\Bundle\GridBundle\Action;

class RedirectAction extends AbstractAction
{
    /**
     * @var string
     */
    protected $type = self::TYPE_REDIRECT;

    /**
     * @var array
     */
    protected $requiredOptions = array('link');
}
