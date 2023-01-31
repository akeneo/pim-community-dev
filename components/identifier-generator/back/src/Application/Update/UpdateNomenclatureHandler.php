<?php

namespace Akeneo\Pim\Automation\IdentifierGenerator\Application\Update;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Validation\CommandValidatorInterface;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\NomenclatureDefinitionRepository;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\NomenclatureValueRepository;

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
        if (null !== $command->getOperator()) {
            $nomenclatureDefinition = $nomenclatureDefinition->withOperator($command->getOperator());
        }

        if (null !== $command->getValue()) {
            $nomenclatureDefinition = $nomenclatureDefinition->withValue($command->getValue());
        }

        $this->nomenclatureDefinitionRepository->update('family', $nomenclatureDefinition);

        foreach ($command->getValues() as $familyCode => $value) {
            $this->nomenclatureValueRepository->set($familyCode, $value === '' ? null : $value);
        }
    }
}
