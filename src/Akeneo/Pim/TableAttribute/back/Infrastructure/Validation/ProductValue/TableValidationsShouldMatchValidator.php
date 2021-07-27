<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Infrastructure\Validation\ProductValue;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TableConfiguration;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Domain\Value\Cell;
use Akeneo\Pim\TableAttribute\Infrastructure\Value\TableValue;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Constraints;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

final class TableValidationsShouldMatchValidator extends ConstraintValidator
{
    private TableConfigurationRepository $tableConfigurationRepository;

    public function __construct(TableConfigurationRepository $tableConfigurationRepository)
    {
        $this->tableConfigurationRepository = $tableConfigurationRepository;
    }

    public function validate($tableValue, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, TableValidationsShouldMatch::class);
        if (!$tableValue instanceof TableValue) {
            return;
        }

        $tableConfiguration = $this->tableConfigurationRepository->getByAttributeCode($tableValue->getAttributeCode());
        $context = $this->context;
        $validator = $context->getValidator()->inContext($context);

        $table = $tableValue->getData();
        foreach ($table as $rowIndex => $row) {
            /** @var Cell $cell */
            foreach ($row as $stringColumnCode => $cell) {
                $constraints = $this->buildConstraints($tableConfiguration, $stringColumnCode);

                if (0 < count($constraints)) {
                    $validator
                        ->atPath(sprintf('[%d].%s', $rowIndex, $stringColumnCode))
                        ->validate($cell->normalize(), $constraints);
                }
            }
        }
    }

    /**
     * @return Constraint[]
     */
    private function buildConstraints(TableConfiguration $tableConfiguration, string $stringColumnCode): array
    {
        $validations = $tableConfiguration->getValidations(ColumnCode::fromString($stringColumnCode));
        if (null === $validations) {
            return [];
        }

        $constraints = [];
        foreach ($validations->normalize() as $key => $validationValue) {
            switch ($key) {
                case 'min':
                    $constraints[] = new Constraints\Range([
                        'min' => $validationValue,
                        'minMessage' => TableValidationsShouldMatch::MIN_MESSAGE,
                    ]);
                    break;
                case 'max':
                    $constraints[] = new Constraints\Range([
                        'max' => $validationValue,
                        'maxMessage' => TableValidationsShouldMatch::MAX_MESSAGE,
                    ]);
                    break;
                case 'decimals_allowed':
                    if (!$validationValue) {
                        $constraints[] = new Constraints\Type([
                            'type' => 'integer',
                            'message' => TableValidationsShouldMatch::DECIMALS_ALLOWED_MESSAGE,
                        ]);
                    }
                    break;
                case 'max_length':
                    $constraints[] = new Constraints\Length([
                        'max' => $validationValue,
                        'maxMessage' => TableValidationsShouldMatch::MAX_LENGTH_MESSAGE,
                    ]);
                    break;
                default:
                    throw new \InvalidArgumentException(sprintf('The "%s" validation is unknown', $key));
            }
        }

        return $constraints;
    }
}
