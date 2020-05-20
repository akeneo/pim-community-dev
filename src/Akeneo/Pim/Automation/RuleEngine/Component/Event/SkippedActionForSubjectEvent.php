<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Component\Event;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\ActionInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * Event dispatched when an action cannot be applied to a given entity
 */
class SkippedActionForSubjectEvent
{
    /** @var ActionInterface */
    private $action;

    /** @var EntityWithValuesInterface */
    private $subject;

    /** @var string */
    private $reason;

    public function __construct(ActionInterface $action, EntityWithValuesInterface $subject, string $reason)
    {
        $this->action = $action;
        $this->subject = $subject;
        $this->reason = $reason;
    }

    public function getAction(): ActionInterface
    {
        return $this->action;
    }

    public function getSubject(): EntityWithValuesInterface
    {
        return $this->subject;
    }

    public function getReason(): string
    {
        return $this->reason;
    }
}
