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

namespace Akeneo\ReferenceEntity\Infrastructure\Validation\Attribute\Url;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Validation;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\Url\PreviewType as PreviewTypeModel;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class PreviewTypeValidator extends ConstraintValidator
{
    public function validate($previewType, Constraint $constraint)
    {
        if (!$constraint instanceof PreviewType) {
            throw new UnexpectedTypeException($constraint, self::class);
        }

        $validator = Validation::createValidator();
        $violations = $validator->validate($previewType, [
            new Assert\Type('string'),
        ]);

        if (!in_array($previewType, PreviewTypeModel::PREVIEW_TYPES)) {
            $this->context->buildViolation(PreviewType::MESSAGE_NOT_EXPECTED_PREVIEW_TYPE)
                ->setParameter(
                    '%preview_types%',
                    implode(', ', PreviewTypeModel::PREVIEW_TYPES)
                )
                ->addViolation();
        }

        if ($violations->count() > 0) {
            foreach ($violations as $violation) {
                $this->context->addViolation(
                    $violation->getMessage(),
                    $violation->getParameters()
                );
            }
        }
    }
}
