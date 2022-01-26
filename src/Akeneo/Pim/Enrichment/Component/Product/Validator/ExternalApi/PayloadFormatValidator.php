<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\ExternalApi;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidator;

final class PayloadFormatValidator extends ConstraintValidator
{
    /** @var array<string, string> */
    private array $attributeTypeByCodes = [];
    private const WRONG_LOCALE_FORMAT = 'Property "%s" expects an array with the key "locale" as string. Check the expected format on the API documentation.';
    private const WRONG_SCOPE_FORMAT = 'Property "%s" expects an array with the key "scope" as string. Check the expected format on the API documentation.';

    public function __construct(private AttributeRepositoryInterface $attributeRepository)
    {
    }

    public function validate($data, Constraint $constraint): void
    {
        $values = \is_array($data['values'] ?? null) ? $data['values'] : [];
        $this->cacheAttributeTypeByCodes(\array_keys($values));

        $contextualValidator = $this->context->getValidator()->inContext($this->context);

        $contextualValidator->validate($data, [
            new Type(['type' => 'array']),
            new NotNull(),
            new Collection([
                'allowExtraFields' => true,
                'allowMissingFields' => true,
                'fields' => [
                    'values' => [
                        new Collection([
                            'allowExtraFields' => true,
                            'fields' => $this->getValuesConstraints($values),
                        ]),
                    ],
                ],
            ]),
        ]);
    }

    /**
     * @param string[] $attributeCodes
     */
    private function cacheAttributeTypeByCodes(array $attributeCodes): void
    {
        if ([] === $attributeCodes) {
            return;
        }

        $codesToFetch = array_diff($attributeCodes, array_keys($this->attributeTypeByCodes));
        $this->attributeTypeByCodes += $this->attributeRepository->getAttributeTypeByCodes($codesToFetch);
    }

    /**
     * @return Constraint[]
     */
    private function getValuesConstraints(array $values): array
    {
        $constraintsByAttribute = [];
        foreach ($values as $attributeCode => $valuesForAttribute) {
            $attributeType = $this->attributeTypeByCodes[$attributeCode] ?? null;
            $dataConstraints = [];

            if (AttributeTypes::PRICE_COLLECTION === $attributeType) {
                $wrongDataFormatMessage = \sprintf('The data format sent for the "%s" attribute is wrong. Please, fill in one value per amount field.', $attributeCode);

                $dataConstraints = [
                    new Type(['type' => 'array', 'message' => $wrongDataFormatMessage]),
                    new NotNull(['message' => $wrongDataFormatMessage]),
                    new All([
                        new Collection([
                            'fields' => [
                                'amount' => [
                                    new Type(['type' => ['string', 'int', 'float', 'null'], 'message' => $wrongDataFormatMessage]),
                                ],
                                'currency' => [
                                    new Type(['type' => 'string', 'message' => $wrongDataFormatMessage]),
                                    new NotNull(['message' => $wrongDataFormatMessage]),
                                ],
                            ],
                        ]),
                    ]),
                ];
            }

            $constraintsByAttribute[$attributeCode] = [
                new All([
                    new Collection([
                        'extraFieldsMessage' => \sprintf('Property "%s" does not expect the {{ field }} field', $attributeCode),
                        'missingFieldsMessage' => \sprintf(
                            'Property "%s" expects an array with the key {{ field }}. Check the expected format on the API documentation.',
                            $attributeCode,

                        ),
                        'fields' => [
                            'locale' => [new Type(['type' => 'string', 'message' => \sprintf(self::WRONG_LOCALE_FORMAT, $attributeCode)])],
                            'scope' => [new Type(['type' => 'string', 'message' => \sprintf(self::WRONG_SCOPE_FORMAT, $attributeCode)])],
                            'data' => $dataConstraints,
                        ],
                    ]),
                ]),
            ];
        }

        return $constraintsByAttribute;
    }
}
