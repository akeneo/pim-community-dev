<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Infrastructure\Validation\Record;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class UpdatedDateValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($filters, Constraint $constraint)
    {
        // Get updated from filters
//        if (null === $filters['updated']) {
//            $this->context->buildViolation(UpdatedDateShouldBeValid::ERROR_MESSAGE)
//                ->setParameter('updated', [])
//                ->atPath('channel')
//                ->addViolation();
//        }
    }
}
