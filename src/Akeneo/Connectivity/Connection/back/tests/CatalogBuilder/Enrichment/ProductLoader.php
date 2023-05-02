<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Enrichment;

use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Messenger\TraceableMessageBus;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ProductLoader
{
    public function __construct(private ProductBuilderInterface $builder, private ObjectUpdaterInterface $updater, private SaverInterface $saver, private ValidatorInterface $validator, private Client $client, private TraceableMessageBus $messageBus)
    {
    }

    public function createWithUuid(?string $uuid, ?string $identifier, array $data): ProductInterface
    {
        $family = $data['family'] ?? null;

        $product = $this->builder->createProduct($identifier, $family, $uuid);
        $this->update($product, $data);

        $this->messageBus->reset();

        return $product;
    }

    public function create(?string $identifier, array $data): ProductInterface
    {
        return $this->createWithUuid(Uuid::uuid4()->toString(), $identifier, $data);
    }

    public function update(ProductInterface $product, array $data): void
    {
        $this->updater->update($product, $data);

        $constraints = $this->validator->validate($product);
        Assert::assertCount(0, $constraints, 'The validation from the product creation failed.');

        $this->saver->save($product);

        $this->client->refreshIndex();
        $this->messageBus->reset();
    }
}
