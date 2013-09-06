<?php

namespace Oro\Bundle\GridBundle\Action;

class AjaxAction extends AbstractAction
{
    /**
     * @var string
     */
    protected $type = self::TYPE_AJAX;

    /**
     * @var array
     */
    protected $requiredOptions = array('link');

    /**
     * @return array
     */
    public function getOptions()
    {
        $options = parent::getOptions();

        $options['frontend_type'] = 'ajax';

        return $options;
    }
}
