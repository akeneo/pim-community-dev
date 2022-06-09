<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Enrichment;

use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PHPUnit\Framework\Assert;
use Symfony\Component\Messenger\TraceableMessageBus;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ProductLoader
{
    private ProductBuilderInterface $builder;
    private ObjectUpdaterInterface $updater;
    private SaverInterface $saver;
    private ValidatorInterface $validator;
    private Client $client;
    private TraceableMessageBus $messageBus;

    public function __construct(
        ProductBuilderInterface $builder,
        ObjectUpdaterInterface $updater,
        SaverInterface $saver,
        ValidatorInterface $validator,
        Client $client,
        TraceableMessageBus $messageBus
    ) {
        $this->builder = $builder;
        $this->updater = $updater;
        $this->saver = $saver;
        $this->validator = $validator;
        $this->client = $client;
        $this->messageBus = $messageBus;
    }

    public function create($identifier, array $data)
    {
        $family = isset($data['family']) ? $data['family'] : null;

        $product = $this->builder->createProduct($identifier, $family);
        $this->update($product, $data);

        $this->messageBus->reset();

        return $product;
    }

    public function update($product, array $data): void
    {
        $this->updater->update($product, $data);

        $constraints = $this->validator->validate($product);
        Assert::assertCount(0, $constraints, 'The validation from the product creation failed.');

        $this->saver->save($product);

        $this->client->refreshIndex();
        $this->messageBus->reset();
    }
}
