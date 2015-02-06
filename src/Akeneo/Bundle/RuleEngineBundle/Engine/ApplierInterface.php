<?php

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
 * Applies a rule.
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
interface ApplierInterface
{
    /**
     * @param RuleInterface           $rule
     * @param RuleSubjectSetInterface $subjectSet
     *
     * @return mixed
     */
    public function apply(RuleInterface $rule, RuleSubjectSetInterface $subjectSet);
}
