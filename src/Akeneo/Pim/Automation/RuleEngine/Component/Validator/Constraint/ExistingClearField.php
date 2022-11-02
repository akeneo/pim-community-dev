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

namespace Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class ExistingClearField extends Constraint
{
    /** @var string */
    public $message = 'pimee_catalog_rule.rule_definition.validation.actions.clear.invalid_field';

    /**
     * {@inheritdoc}
     */
    public function validatedBy(): string
    {
        return 'pimee_clear_fields_validator';
    }
}
