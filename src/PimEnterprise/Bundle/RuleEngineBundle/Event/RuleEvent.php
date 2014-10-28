<?php

namespace PimEnterprise\Bundle\RuleEngineBundle\Event;

use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Rule event
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class RuleEvent extends Event
{
    protected $rule;

    public function __construct(RuleInterface $rule)
    {
        $this->rule = $rule;
    }

    public function getRule()
    {
        return $this->rule;
    }
}
