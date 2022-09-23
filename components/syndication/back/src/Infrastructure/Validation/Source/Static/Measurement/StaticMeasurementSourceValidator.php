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

namespace Akeneo\Platform\Syndication\Infrastructure\Validation\Source\Static\Measurement;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\Uuid;
use Symfony\Component\Validator\ConstraintValidator;

class StaticMeasurementSourceValidator extends ConstraintValidator
{
    public function validate($source, Constraint $constraint): void
    {
        $validator = $this->context->getValidator();
        $sourceConstraintFields = [
            'uuid' => [
                new NotBlank(),
                new Uuid()
            ],
            'code' => new EqualTo('measurement'),
            'value' => new Collection(['fields' => [
                'value' => new Type('string'),
                'unit' => new Type('string'), // We should restrict the unit to a one in the target measurement family
            ]]),
            'type' => new EqualTo('static'),
        ];
        $sourceConstraintFields['selection'] = new Collection(['fields' => [
            'type' => new EqualTo(['value' => 'code'])
        ]]);
        $sourceConstraintFields['operations'] = new Collection(['fields' => []]);

        $violations = $validator->validate($source, new Collection(['fields' => $sourceConstraintFields]));

        foreach ($violations as $violation) {
            $this->context->buildViolation(
                $violation->getMessage(),
                $violation->getParameters()
            )
                ->atPath($violation->getPropertyPath())
                ->addViolation();
        }
    }
}
