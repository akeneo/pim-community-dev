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

namespace Akeneo\Test\Pim\Automation\RuleEngine\Acceptance\Context;

use Akeneo\Tool\Bundle\RuleEngineBundle\Event\RuleEvents;
use Akeneo\Tool\Bundle\RuleEngineBundle\Event\SkippedSubjectRuleEvent;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleInterface;
use Behat\Behat\Context\Context;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
class EventSubscriberContext implements Context, EventSubscriberInterface
{
    private static $skipRulesForEntities = [];

    public static function getSubscribedEvents()
    {
        return [
            RuleEvents::SKIP => 'addSkipRuleExecution',
        ];
    }

    /**
     * @BeforeScenario
     */
    public static function clean(): void
    {
        static::$skipRulesForEntities = [];
    }

    public function addSkipRuleExecution(SkippedSubjectRuleEvent $event): void
    {
        static::$skipRulesForEntities[] = [
            'subject' => $event->getSubject()->getIdentifier(),
            'rule' => $event->getDefinition()->getCode(),
            'reasons' => $event->getReasons(),
        ];
    }

    public static function assertNoSkipExecutionForRuleAndEntity(RuleInterface $rule, $subject): void
    {
        foreach (static::$skipRulesForEntities as $skipped) {
            if ($rule->getCode() === $skipped['rule'] && $subject->getIdentifier() === $skipped['subject']) {
                throw new \LogicException(sprintf(
                    'The "%s" rule was not executed for the "%s" product for these following reason(s): %s',
                    $rule->getCode(),
                    $subject->getIdentifier(),
                    implode(', ', $skipped['reasons'])
                ));
            }
        }
    }
}
