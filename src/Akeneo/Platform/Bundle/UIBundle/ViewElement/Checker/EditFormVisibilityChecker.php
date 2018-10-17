<?php

namespace Akeneo\Platform\Bundle\UIBundle\ViewElement\Checker;

/**
 * Displays a view element only in an edit form context
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EditFormVisibilityChecker extends NonEmptyPropertyVisibilityChecker
{
    /** @staticvar string */
    const PATH = '[form].vars[value].id';

    /**
     * {@inheritdoc}
     */
    public function isVisible(array $config = [], array $context = [])
    {
        $config['property'] = isset($config['path']) ? $config['path'] : static::PATH;

        return parent::isVisible($config, $context);
    }
}
