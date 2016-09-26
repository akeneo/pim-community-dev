<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ActivityManager\Bundle\DependencyInjection\Compiler;

use Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\Compiler\AbstractResolveDoctrineTargetModelPass;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class ResolveDoctrineTargetModelPass extends AbstractResolveDoctrineTargetModelPass
{
    protected function getParametersMapping()
    {
        return [
            'Akeneo\ActivityManager\Component\Model\ProjectInterface' => 'akeneo_activity_manager.model.project.class'
        ];
    }
}
