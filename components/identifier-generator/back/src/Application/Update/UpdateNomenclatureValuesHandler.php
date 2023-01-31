<?php

namespace Akeneo\Pim\Automation\IdentifierGenerator\Application\Update;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\NomenclatureValueRepository;

final class UpdateNomenclatureValuesHandler
{
    public function __construct(
        private readonly NomenclatureValueRepository $nomenclatureValueRepository,
    ) {
    }

    public function __invoke(UpdateNomenclatureValuesCommand $command): void
    {
        foreach ($command->getValues() as $familyCode => $value) {
            $this->nomenclatureValueRepository->set($familyCode, $value);
        }
    }
}
