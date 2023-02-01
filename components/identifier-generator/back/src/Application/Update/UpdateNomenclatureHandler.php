<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Application\Update;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Validation\CommandValidatorInterface;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\NomenclatureDefinition;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\NomenclatureDefinitionRepository;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\NomenclatureValueRepository;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UpdateNomenclatureHandler
{
    public function __construct(
        private readonly NomenclatureValueRepository $nomenclatureValueRepository,
        private readonly NomenclatureDefinitionRepository $nomenclatureDefinitionRepository,
        private readonly CommandValidatorInterface $validator,
    ) {
    }

    public function __invoke(UpdateNomenclatureCommand $command): void
    {
        $this->validator->validate($command);

        $nomenclatureDefinition = $this->nomenclatureDefinitionRepository->get('family');
        if (null === $nomenclatureDefinition) {
            $nomenclatureDefinition = new NomenclatureDefinition();
        }

        Assert::notNull($command->getOperator());
        Assert::notNull($command->getValue());
        Assert::notNull($command->getGenerateIfEmpty());

        $nomenclatureDefinition = $nomenclatureDefinition
            ->withOperator($command->getOperator())
            ->withValue($command->getValue())
            ->withGenerateIfEmpty($command->getGenerateIfEmpty());

        $this->nomenclatureDefinitionRepository->update('family', $nomenclatureDefinition);

        $this->nomenclatureValueRepository->update($command->getPropertyCode(), $command->getValues());
    }
}
