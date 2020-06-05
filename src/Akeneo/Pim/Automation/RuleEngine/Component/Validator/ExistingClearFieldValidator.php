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

namespace Akeneo\Pim\Automation\RuleEngine\Component\Validator;

use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\ExistingClearField;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Clearer\ClearerRegistryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class ExistingClearFieldValidator extends ConstraintValidator
{
    /** @var ClearerRegistryInterface */
    private $clearerRegistry;

    public function __construct(ClearerRegistryInterface $clearerRegistry)
    {
        $this->clearerRegistry = $clearerRegistry;
    }

    public function validate($value, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, ExistingClearField::class);
        if (null === $value || !is_string($value)) {
            return;
        }

        if (null === $this->clearerRegistry->getClearer($value)) {
            $this->context->buildViolation($constraint->message, ['%field%' => $value])
                ->addViolation();

            return;
        }
    }
}
