<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Tool\Bundle\RuleEngineBundle\Event;

use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Rule event
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class RuleEvent extends Event
{
    /** @var RuleDefinitionInterface */
    protected $definition;

    /**
     * @param RuleDefinitionInterface $definition
     */
    public function __construct(RuleDefinitionInterface $definition)
    {
        $this->definition = $definition;
    }

    /**
     * Get rule
     *
     * @return RuleDefinitionInterface
     */
    public function getDefinition()
    {
        return $this->definition;
    }
}
