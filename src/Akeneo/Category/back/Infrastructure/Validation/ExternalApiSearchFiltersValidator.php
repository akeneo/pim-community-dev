<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Validation;

use Akeneo\Pim\Enrichment\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ExternalApiSearchFiltersValidator
{
    public function __construct(
        private readonly ValidatorInterface $validator,
    ) {
    }

    public function validate(array $searchFilters): void
    {
        if (empty($searchFilters)) {
            return;
        }

        $constraints = [
            'code' => new Assert\All([
                new Assert\Collection([
                    'operator' => new Assert\IdenticalTo([
                        'value' => 'IN',
                        'message' => 'In order to search on category codes you must use "IN" operator, {{ value }} given.',
                    ]),
                    'value' => [
                        new Type([
                            'type' => 'array',
                            'message' => 'In order to search on category codes you must send an array of category codes as value, {{ givenType }} given.',
                        ]),
                        new Assert\All([
                            new Assert\Type('string'),
                        ]),
                    ],
                ]),
            ]),
            'parent' => new Assert\All([
                new Assert\Collection([
                    'operator' => new Assert\IdenticalTo([
                        'value' => '=',
                        'message' => 'In order to search on category parent you must use "=" operator, {{ value }} given.',
                    ]),
                    'value' => [
                        new Assert\Type([
                            'type' => 'string',
                            'message' => 'In order to search on category parent you must send a parent code category as value, {{ type }} given.',
                        ]),
                    ],
                ]),
            ]),
            'is_root' => new Assert\All([
                new Assert\Collection([
                    'operator' => new Assert\IdenticalTo([
                        'value' => '=',
                        'message' => 'In order to search on category is_root you must use "=" operator, {{ value }} given.',
                    ]),
                    'value' => [
                        new Assert\Type([
                            'type' => 'bool',
                            'message' => 'In order to search on category is_root you must send a {{ type }} value, {{ value }} given.',
                        ]),
                    ],
                ]),
            ]),
            'updated' => new Assert\All([
                new Assert\Collection([
                    'operator' => new Assert\IdenticalTo([
                        'value' => '>',
                        'message' => 'Searching on the "updated" property require the ">" (greater than) operator, {{ value }} given.',
                    ]),
                    'value' => new Assert\DateTime([
                        'format' => \DateTimeInterface::ATOM,
                        'message' => 'This value is not in a valid ISO 8601 standard datetime format',
                    ]),
                ]),
            ]),
        ];
        $availableSearchFilters = array_keys($constraints);

        $exceptionMessages = [];
        foreach ($searchFilters as $property => $searchFilter) {
            if (!in_array($property, $availableSearchFilters, true)) {
                throw new \InvalidArgumentException(sprintf('Available search filters are "%s" and you tried to search on unavailable filter "%s"', implode(', ', $availableSearchFilters), $property));
            }
            $violations = $this->validator->validate($searchFilter, $constraints[$property]);
            foreach ($violations as $violation) {
                $exceptionMessages[] = $violation->getMessage();
            }
        }

        if (!empty($exceptionMessages)) {
            throw new \InvalidArgumentException(implode(' ', $exceptionMessages));
        }
    }
}
