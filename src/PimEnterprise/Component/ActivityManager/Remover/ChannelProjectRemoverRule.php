<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ActivityManager\Remover;

use Pim\Component\Catalog\Model\ChannelInterface;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;

/**
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class ChannelProjectRemoverRule implements ProjectRemoverRuleInterface
{
    /**
     * A project has to be removed if its channel is removed.
     *
     * {@inheritdoc}
     */
    public function hasToBeRemoved(ProjectInterface $project, $channel)
    {
        return $channel instanceof ChannelInterface && $project->getChannel()->getCode() === $channel->getCode();
    }
}
