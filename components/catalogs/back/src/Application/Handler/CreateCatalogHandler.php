<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Handler;

use Akeneo\Catalogs\Application\Persistence\UpdateCatalogProductSelectionCriteriaQueryInterface;
use Akeneo\Catalogs\Application\Persistence\UpsertCatalogQueryInterface;
use Akeneo\Catalogs\ServiceAPI\Command\CreateCatalogCommand;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CreateCatalogHandler
{
    public function __construct(
        private UpsertCatalogQueryInterface $upsertCatalogQuery,
        private UpdateCatalogProductSelectionCriteriaQueryInterface $updateCatalogProductSelectionCriteriaQuery,
    ) {
    }

    public function __invoke(CreateCatalogCommand $command): void
    {
        $this->upsertCatalogQuery->execute(
            $command->getId(),
            $command->getName(),
            $command->getOwnerUsername(),
            false,
        );

        $this->updateCatalogProductSelectionCriteriaQuery->execute(
            $command->getId(),
            [
                [
                    'field' => 'enabled',
                    'operator' => '=',
                    'value' => true,
                ],
            ],
        );
    }
}
