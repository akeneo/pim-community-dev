<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\AssetManager\Component\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * Validator to ensure there is no more assets in the product value than the limit defined.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class ThereShouldBeLessAssetsInValueThanLimitValidator extends ConstraintValidator
{
    /** @var int */
    private $maxAssets;

    public function __construct(string $maxAssets)
    {
        $this->maxAssets = (int) $maxAssets;
    }

    public function validate($value, Constraint $constraint)
    {
        Assert::isInstanceOf($constraint, ThereShouldBeLessAssetsInValueThanLimit::class);
        $data = $value->getData();

        if (is_array($data) && count($data) > $this->maxAssets) {
            $this->context->buildViolation(
                $constraint->message,
                [
                    '%limit%' => $this->maxAssets,
                ]
            )->addViolation();
        }
    }
}
