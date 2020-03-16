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

namespace Akeneo\Tool\Bundle\RuleEngineBundle\Validation;

use Symfony\Component\Validator\Constraint;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class UniqueRuleDefinitionCode extends Constraint
{
    public $message = 'This value is already used.';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return UniqueRuleDefinitionCodeValidator::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
