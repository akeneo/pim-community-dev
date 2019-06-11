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

use Akeneo\ReferenceEntity\Domain\Model\Attribute\Url\MediaType as MediaTypeModel;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Validation;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class MediaTypeValidator extends ConstraintValidator
{
    public function validate($mediaType, Constraint $constraint)
    {
        if (!$constraint instanceof MediaType) {
            throw new UnexpectedTypeException($constraint, self::class);
        }

        $validator = Validation::createValidator();
        $violations = $validator->validate($mediaType, [
            new Assert\Type('string'),
        ]);

        if (!in_array($mediaType, MediaTypeModel::MEDIA_TYPES)) {
            $this->context->buildViolation(MediaType::MESSAGE_NOT_EXPECTED_MEDIA_TYPE)
                ->setParameter(
                    '%supported_media_types%',
                    implode(', ', MediaTypeModel::MEDIA_TYPES)
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
