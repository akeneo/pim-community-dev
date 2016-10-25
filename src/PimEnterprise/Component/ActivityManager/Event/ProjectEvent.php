<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ActivityManager\Component\Event;

use Akeneo\ActivityManager\Component\Model\ProjectInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Project create event.
 *
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class ProjectEvent extends Event
{
    /** @var ProjectInterface */
    private $project;

    /**
     * @param ProjectInterface $project
     */
    public function __construct(ProjectInterface $project)
    {
        $this->project = $project;
    }

    /**
     * @return ProjectInterface
     */
    public function getProject()
    {
        return $this->project;
    }
}
