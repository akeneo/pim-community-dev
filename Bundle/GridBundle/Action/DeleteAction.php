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
     * @return array
     */
    public function getOptions()
    {
        $options = parent::getOptions();

        if (!isset($options['confirmation'])) {
            $options['confirmation'] = true;
        }

        return $options;
    }
}
