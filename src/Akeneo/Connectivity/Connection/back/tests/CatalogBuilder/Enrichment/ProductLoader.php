<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Enrichment;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\UniqueValuesSet;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ProductLoader
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly MessageBusInterface $productMessageBus,
        private readonly UniqueValuesSet $uniqueValuesSet
    ) {
    }

    /**
     * @param UserIntent[] $userIntents
     */
    public function create(string $identifier, array $userIntents = []) : ProductInterface
    {
        return $this->createOrUpdate($identifier, $userIntents);
    }

    /**
     * @param UserIntent[] $userIntents
     */
    public function update(string $identifier, array $userIntents = []) : ProductInterface
    {
        return $this->createOrUpdate($identifier, $userIntents);
    }

    /**
     * @param UserIntent[] $userIntents
     */
    public function createOrUpdate(string $identifier, array $userIntents = []) : ProductInterface
    {
        $this->uniqueValuesSet->reset();

        $this->productMessageBus->dispatch(
            UpsertProductCommand::createWithIdentifierSystemUser($identifier, $userIntents)
        );

        return $this->productRepository->findOneByIdentifier($identifier);
    }
}
