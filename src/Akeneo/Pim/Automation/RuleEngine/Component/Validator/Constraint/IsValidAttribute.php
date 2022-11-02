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

use Akeneo\Pim\Automation\RuleEngine\Component\Validator\IsValidAttributeValidator;
use Symfony\Component\Validator\Constraint;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class IsValidAttribute extends Constraint
{
    public $attributeProperty;
    public $channelProperty;
    public $localeProperty;

    /**
     * {@inheritdoc}
     */
    public function validatedBy(): string
    {
        return IsValidAttributeValidator::class;
    }

    public function getTargets(): string|array
    {
        return [static::CLASS_CONSTRAINT];
    }

    public function getRequiredOptions(): array
    {
        return ['attributeProperty', 'channelProperty', 'localeProperty'];
    }
}
