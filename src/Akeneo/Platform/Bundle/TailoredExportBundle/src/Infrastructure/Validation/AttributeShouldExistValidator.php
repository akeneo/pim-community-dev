<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Infrastructure\Validation;

use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

class AttributeShouldExistValidator extends ConstraintValidator
{
    private GetAttributes $getAttributes;

    public function __construct(GetAttributes $getAttributes)
    {
        $this->getAttributes = $getAttributes;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($attributeCode, Constraint $constraint)
    {
        Assert::isInstanceOf($constraint, AttributeShouldExist::class);
        if (!is_string($attributeCode)) {
            return;
        }

        if (in_array($attributeCode, ['parent', 'groups', 'categories', 'enabled', 'family', 'family_variant'])) {
            return;
        }

        if (null === $this->getAttributes->forCode($attributeCode)) {
            $this->context->buildViolation(
                AttributeShouldExist::NOT_EXIST_MESSAGE,
                [
                    '{{ attribute_code }}' => $attributeCode,
                ]
            )->addViolation();
        }
    }
}
