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

use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductAddActionInterface;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\ChannelShouldExist;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\IsValidAttribute;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\LocaleShouldBeActive;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\ProductAddAction;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Validator\ContextualValidatorInterface;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class ProductAddActionValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($action, Constraint $constraint)
    {
        Assert::implementsInterface($action, ProductAddActionInterface::class);
        Assert::isInstanceOf($constraint, ProductAddAction::class);

        $context = $this->context;
        $validator = $context->getValidator()->inContext($context);

        $locale = $action->getOptions()['locale'] ?? null;
        $scope = $action->getOptions()['scope'] ?? null;

        $this->validateAttributeField($validator, $action->getField(), $locale, $scope);
        $validator->atPath('locale')->validate($locale, new LocaleShouldBeActive());
        $validator->atPath('scope')->validate($scope, new ChannelShouldExist());
    }

    private function validateAttributeField(
        ContextualValidatorInterface $validator,
        ?string $attributeCode,
        ?string $locale,
        ?string $scope
    ): void {
        $validator->atPath('field')->validate($attributeCode, new IsValidAttribute([
            'locale' => $locale,
            'scope' => $scope,
            'errorOnAttributeNotFound' => false,
        ]));
    }
}
