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

    /**
     * {@inheritDoc}
     *
     * @see \Oro\Bundle\DataGridBundle\Common\IterableObject
     *
     * By default, \Oro\Bundle\DataGridBundle\Common\IterableObject does not do recursive merge on the params value (see
     * merge method). To avoid changing this low-level method, we do a recursive merge in this method, to be able to
     * merge the options and the additional parameters with the sub options.
     */
    public function getOptions()
    {
        $additionalOptions = self::$additionalOptions;
        $options = parent::getOptions();
        foreach (array_keys(self::$additionalOptions) as $key) {
            if ($options->offsetExists($key)) {
                $value = $options->offsetGet($key);
                $value = array_merge($additionalOptions[$key], $value);
                $options->offsetSet($key, $value);
                unset($additionalOptions[$key]);
            }
        }
        $options->merge($additionalOptions);

        return $options;
    }
}
