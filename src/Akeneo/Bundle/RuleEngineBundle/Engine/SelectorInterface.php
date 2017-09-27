<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Bundle\RuleEngineBundle\Engine;

use Akeneo\Bundle\RuleEngineBundle\Model\RuleInterface;
use Akeneo\Bundle\RuleEngineBundle\Model\RuleSubjectSetInterface;

/**
 * Selects subjects impacted by a rule.
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
interface SelectorInterface
{
    /**
     * @param RuleInterface $rule
     *
     * @return RuleSubjectSetInterface
     */
    public function select(RuleInterface $rule): RuleSubjectSetInterface;
}
