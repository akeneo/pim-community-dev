<?php

namespace Akeneo\Pim\Automation\IdentifierGenerator\Application\Update;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\NomenclatureDefinitionRepository;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\NomenclatureValueRepository;

final class UpdateNomenclatureValuesHandler
{
    public function __construct(
        private readonly NomenclatureValueRepository $nomenclatureValueRepository,
        private readonly NomenclatureDefinitionRepository $nomenclatureDefinitionRepository,
    ) {
    }

    public function __invoke(UpdateNomenclatureValuesCommand $command): void
    {
        $nomenclatureDefinition = $this->nomenclatureDefinitionRepository->get('family');
        if (null !== $command->getOperator()) {
            $nomenclatureDefinition = $nomenclatureDefinition->withOperator($command->getOperator());
        }

        if (null !== $command->getValue()) {
            $nomenclatureDefinition = $nomenclatureDefinition->withValue($command->getValue());
        }

        $this->nomenclatureDefinitionRepository->update('family', $nomenclatureDefinition);

        foreach ($command->getValues() as $familyCode => $value) {
            $this->nomenclatureValueRepository->set($familyCode, $value);
        }
    }
}
