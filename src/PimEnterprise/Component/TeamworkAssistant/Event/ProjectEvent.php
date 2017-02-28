<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\TeamworkAssistant\Event;

use PimEnterprise\Component\TeamworkAssistant\Model\ProjectInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Project create event.
 *
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class ProjectEvent extends Event
{
    /** @var ProjectInterface */
    protected $project;

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
