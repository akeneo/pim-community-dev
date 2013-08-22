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

    /**
     * @param array $options
     */
    public function setOptions(array $options)
    {
        if (!isset($options['confirmation'])) {
            $options['confirmation'] = true;
        }

        parent::setOptions($options);
    }
}
