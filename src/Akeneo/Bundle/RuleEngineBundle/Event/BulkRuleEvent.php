<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Bundle\RuleEngineBundle\Event;

use Akeneo\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event with many concerned rules
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class BulkRuleEvent extends Event
{
    /** @var RuleDefinitionInterface[] */
    protected $definitions;

    /**
     * @param RuleDefinitionInterface[] $definitions
     */
    public function __construct(array $definitions)
    {
        $this->definitions = $definitions;
    }

    /**
     * Get rule definitions
     *
     * @return RuleDefinitionInterface[]
     */
    public function getDefinitions()
    {
        return $this->definitions;
    }
}
