<?php

namespace Akeneo\Test\Acceptance\AssociationType;

use Akeneo\Pim\Enrichment\Component\Product\Query\FindNonExistingAssociationTypeCodesQueryInterface;
use Akeneo\Pim\Structure\Component\Model\AssociationType;

class InMemoryFindNonExistingAssociationTypeCodesQuery implements FindNonExistingAssociationTypeCodesQueryInterface
{
    /** @var InMemoryAssociationTypeRepository */
    private $associationTypeRepository;

    public function __construct(
        InMemoryAssociationTypeRepository $associationTypeRepository
    ) {
        $this->associationTypeRepository = $associationTypeRepository;
    }

    public function execute(array $codes): array
    {
        $existingCodes = $this->getAllAssociationTypeCodes();

        $nonExistingCodes = array_values(array_diff($codes, $existingCodes));

        return $nonExistingCodes;
    }

    private function getAllAssociationTypeCodes(): array
    {
        $associationTypes = $this->associationTypeRepository->findAll();

        return array_map(function (AssociationType $associationType) {
            return $associationType->getCode();
        }, $associationTypes);
    }
}
