<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Infrastructure\Validation;

use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetParent;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ParentShouldExistValidator extends ConstraintValidator
{
    public function __construct(private ProductModelRepositoryInterface $productModelRepository)
    {
    }

    public function validate($parentUserIntent, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, ParentShouldExist::class);

        if (!$parentUserIntent instanceof SetParent) {
            return;
        }

        $parent = $this->productModelRepository->findOneBy(['code' => $parentUserIntent->parentCode()]);
        if (null === $parent) {
            $this->context->buildViolation($constraint->message, ['{{ parentCode }}' => $parentUserIntent->parentCode()])->addViolation();
        }
    }
}
