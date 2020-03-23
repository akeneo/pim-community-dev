<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\AssociationType;

use Akeneo\Pim\Enrichment\Component\Product\Query\AssociationType\GetAssociationTypeCodes;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class InMemoryGetAssociationTypeCodes implements GetAssociationTypeCodes
{
    /** @var InMemoryAssociationTypeRepository */
    private $associationTypeRepository;

    public function __construct(InMemoryAssociationTypeRepository $associationTypeRepository)
    {
        $this->associationTypeRepository = $associationTypeRepository;
    }

    public function findAll(): \Iterator
    {
        foreach ($this->associationTypeRepository->findAll() as $associationType) {
            yield $associationType->getCode();
        }
    }
}
