<?php

namespace Akeneo\Test\Acceptance\AssociationType;

use Akeneo\Pim\Enrichment\Component\Product\Query\FindQuantifiedAssociationTypeCodesInterface;
use Akeneo\Pim\Structure\Component\Model\AssociationType;

class InMemoryFindQuantifiedAssociationTypeCodes implements FindQuantifiedAssociationTypeCodesInterface
{
    /** @var InMemoryAssociationTypeRepository */
    private $associationTypeRepository;

    public function __construct(InMemoryAssociationTypeRepository $associationTypeRepository)
    {
        $this->associationTypeRepository = $associationTypeRepository;
    }

    public function execute(): array
    {
        $associationTypes = $this->associationTypeRepository->findAll();

        return array_map(function (AssociationType $associationType) {
            return $associationType->getCode();
        }, $associationTypes);
    }
}
