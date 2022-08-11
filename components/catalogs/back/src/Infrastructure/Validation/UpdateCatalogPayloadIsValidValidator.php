<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Validation;

use Akeneo\Catalogs\Application\Persistence\GetChannelLocalesQueryInterface;
use Akeneo\Catalogs\Infrastructure\Validation\CatalogPayload\CompletenessFieldIsValid;
use Akeneo\Catalogs\Infrastructure\Validation\CatalogPayload\EnabledFieldIsValid;
use Akeneo\Catalogs\Infrastructure\Validation\CatalogPayload\FamilyFieldIsValid;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class UpdateCatalogPayloadIsValidValidator extends ConstraintValidator
{
    public function __construct(private GetChannelLocalesQueryInterface $getChannelLocalesQuery)
    {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof UpdateCatalogPayloadIsValid) {
            throw new UnexpectedTypeException($constraint, UpdateCatalogPayloadIsValid::class);
        }

        $this->context
            ->getValidator()
            ->inContext($this->context)
            ->validate($value, $this->getConstraints());
    }

    /**
     * @return array<array-key, Constraint>
     */
    private function getConstraints(): array
    {
        return [
            new Assert\Collection([
                'fields' => [
                    'enabled' => new Assert\Required([
                        new Assert\Type('boolean'),
                    ]),
                    'product_selection_criteria' => [
                        new Assert\Type('array'),
                        new Assert\Callback(static function (mixed $array, ExecutionContextInterface $context): void {
                            if (!\is_array($array)) {
                                return;
                            }

                            if (\count(\array_filter(\array_keys($array), 'is_string')) > 0) {
                                $context->buildViolation('Invalid array structure.')
                                    ->addViolation();
                            }
                        }),
                        new Assert\All([
                            new Assert\Callback(function (mixed $criterion, ExecutionContextInterface $context): void {
                                if (!\is_array($criterion)) {
                                    return;
                                }

                                $constraints = match ($criterion['field'] ?? null) {
                                    'completeness' => new CompletenessFieldIsValid($this->getChannelLocalesQuery),
                                    'enabled' => new EnabledFieldIsValid(),
                                    'family' => new FamilyFieldIsValid(),
                                    default => null
                                };

                                if (null === $constraints) {
                                    $context->buildViolation('Invalid field value')
                                        ->atPath('[field]')
                                        ->addViolation();

                                    return;
                                }

                                $context
                                    ->getValidator()
                                    ->inContext($this->context)
                                    ->validate($criterion, $constraints);
                            }),
                        ]),
                    ],
                ],
                'allowMissingFields' => false,
                'allowExtraFields' => false,
            ]),
        ];
    }
}
