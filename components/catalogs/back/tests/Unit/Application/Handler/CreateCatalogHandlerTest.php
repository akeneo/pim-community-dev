<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Unit\Application\Handler;

use Akeneo\Catalogs\Application\Handler\CreateCatalogHandler;
use Akeneo\Catalogs\Application\Persistence\UpdateCatalogProductSelectionCriteriaQueryInterface;
use Akeneo\Catalogs\Application\Persistence\UpsertCatalogQueryInterface;
use Akeneo\Catalogs\ServiceAPI\Command\CreateCatalogCommand;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateCatalogHandlerTest extends TestCase
{
    private ?UpsertCatalogQueryInterface $upsertCatalogQuery;
    private ?UpdateCatalogProductSelectionCriteriaQueryInterface $updateCatalogProductSelectionCriteriaQuery;
    private ?CreateCatalogHandler $handler;

    protected function setUp(): void
    {
        $this->upsertCatalogQuery = $this->createMock(UpsertCatalogQueryInterface::class);
        $this->updateCatalogProductSelectionCriteriaQuery = $this->createMock(
            UpdateCatalogProductSelectionCriteriaQueryInterface::class
        );

        $this->handler = new CreateCatalogHandler(
            $this->upsertCatalogQuery,
            $this->updateCatalogProductSelectionCriteriaQuery,
        );
    }

    public function testItCallsTheQueries(): void
    {
        $this->upsertCatalogQuery
            ->expects($this->once())
            ->method('execute')
            ->with(
                id: 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
                name: 'Store US',
                ownerUsername: 'shopifi',
                enabled: false,
            );

        $this->updateCatalogProductSelectionCriteriaQuery
            ->expects($this->once())
            ->method('execute')
            ->with(
                id: 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
                productSelectionCriteria: [
                    [
                        'field' => 'enabled',
                        'operator' => '=',
                        'value' => true,
                    ],
                ],
            );

        $command = new CreateCatalogCommand(
            id: 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            name: 'Store US',
            ownerUsername: 'shopifi',
        );

        ($this->handler)($command);
    }
}
