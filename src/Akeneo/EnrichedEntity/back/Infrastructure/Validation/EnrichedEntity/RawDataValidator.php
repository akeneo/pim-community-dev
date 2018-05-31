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

namespace Akeneo\EnrichedEntity\back\Infrastructure\Validation\EnrichedEntity;

use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Validation;

/**
 * Validator for enriched entity raw data.
 * It ensures that raw data for an enriched entity properties are well formatted.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class RawDataValidator
{
    /**
     * @param $input
     *
     * @return ConstraintViolationListInterface
     */
    public function validate($input)
    {
        $validator = Validation::createValidator();

        $constraint = new Constraints\Collection([
            'fields' => [
                'identifier' => [
                    new Constraints\Callback(['callback' => function ($identifier, ExecutionContextInterface $context) {
                        if ('' === $identifier) {
                            $context->addViolation('This value should not be blank.');
                        }
                    }]),
                    new Constraints\Regex([
                        'pattern' => '/^[a-zA-Z0-9_]+$/',
                        'message' => 'Enriched Entity code may contain only letters, numbers and underscores'
                    ]),
                ],
            ],
            'allowMissingFields' => true,
            'allowExtraFields' => true
        ]);

        return $validator->validate($input, $constraint);
    }
}
