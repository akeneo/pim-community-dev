<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\TableAttribute\Domain\Config\TableConfiguration;
use Akeneo\Pim\TableAttribute\Domain\Config\TextColumn;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * @author Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 */
final class TableAttributeValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        Assert::implementsInterface($value, AttributeInterface::class);
        Assert::isInstanceOf($constraint, TableAttribute::class);
        if (AttributeTypes::TABLE !== $value->getType()) {
            return;
        }

        if (null === $value->getRawTableConfiguration()) {
            $this->context->buildViolation('toto', [])->atPath('table_configuration')->addViolation();
            return;
        }

        try {
            TableConfiguration::fromColumnDefinitions(
                array_map(
                    fn (array $columnDefinitions) => TextColumn::fromNormalized($columnDefinitions),
                    $value->getRawTableConfiguration()
                )
            );
        } catch (\Exception $e) {
            $this->context->buildViolation($e->getMessage(), [])->atPath('table_configuration')->addViolation();
        }

//        $context = $this->context;
//        $validator = $context->getValidator()->inContext($context);
//        $validator->validate($value->getRawTableConfiguration(), [...]);
    }

}
