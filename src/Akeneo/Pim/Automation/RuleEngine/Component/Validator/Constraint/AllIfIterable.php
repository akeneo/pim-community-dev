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

use Akeneo\Pim\Automation\RuleEngine\Component\Validator\AllIfIterableValidator;
use Symfony\Component\Validator\Constraints\Composite;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class AllIfIterable extends Composite
{
    public $constraints = [];

    public function getDefaultOption(): ?string
    {
        return 'constraints';
    }

    public function getRequiredOptions(): array
    {
        return ['constraints'];
    }

    protected function getCompositeOption()
    {
        return 'constraints';
    }

    public function validatedBy(): string
    {
        return AllIfIterableValidator::class;
    }

    public function getTargets(): string|array
    {
        return static::PROPERTY_CONSTRAINT;
    }
}
