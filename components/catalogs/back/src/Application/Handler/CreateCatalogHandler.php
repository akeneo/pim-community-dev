<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Handler;

use Akeneo\Catalogs\Application\Persistence\Catalog\UpsertCatalogQueryInterface;
use Akeneo\Catalogs\Domain\Catalog;
use Akeneo\Catalogs\Domain\Operator;
use Akeneo\Catalogs\ServiceAPI\Command\CreateCatalogCommand;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CreateCatalogHandler
{
    public function __construct(
        private UpsertCatalogQueryInterface $upsertCatalogQuery,
    ) {
    }

    public function __invoke(CreateCatalogCommand $command): void
    {
        $this->upsertCatalogQuery->execute(
            new Catalog(
                $command->getId(),
                $command->getName(),
                $command->getOwnerUsername(),
                false,
                [
                    [
                        'field' => 'enabled',
                        'operator' => Operator::EQUALS,
                        'value' => true,
                    ],
                ],
                [],
                [],
            ),
        );
    }
}
