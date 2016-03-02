<?php

namespace Oro\Bundle\DataGridBundle\Extension\Action\Actions;

class NavigateAction extends AbstractAction
{
    /**
     * @var array
     */
    protected $requiredOptions = ['link'];

    protected static $additionalOptions = [
        'launcherOptions' => [
            'onClickReturnValue' => false,
            'runAction'          => true,
            'className'          => 'no-hash',
        ]
    ];

    public function getOptions()
    {
        $options = parent::getOptions();
        $options->merge(self::$additionalOptions);

        return $options;
    }
}
