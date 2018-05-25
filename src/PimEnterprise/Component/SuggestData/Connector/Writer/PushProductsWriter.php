<?php
declare(strict_types=1);

namespace PimEnterprise\Component\SuggestData\Connector\Writer;

use Akeneo\Component\Batch\Item\FlushableInterface;
use Akeneo\Component\Batch\Item\ItemWriterInterface;
use PimEnterprise\Bundle\SuggestDataBundle\Infra\DataProvider\Adapter\DataProviderAdapterInterface;
use PimEnterprise\Bundle\SuggestDataBundle\Infra\DataProvider\DataProviderFactory;

/**
 * Writer to push products to PIM.ai.
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class PushProductsWriter implements ItemWriterInterface, FlushableInterface
{
    /** @var DataProviderAdapterInterface */
    protected $dataProvider;

    /** @var int */
    protected $batchSize;

    /** @var array */
    protected $productsToPush;

    /** @var array */
    protected $providedData;

    /**
     * @param DataProviderFactory $factory
     * @param int                 $batchSize
     */
    public function __construct(DataProviderFactory $factory, int $batchSize)
    {
        $this->dataProvider = $factory->create();
        $this->batchSize = $batchSize;
        $this->productsToPush = [];
        $this->providedData = [];
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        foreach ($items as $item) {
            $this->productsToPush[] = $item;
            if (0 === count($this->productsToPush) % $this->batchSize) {
                $rawProvidedData = $this->dataProvider->bulkPush($this->productsToPush);

                $providedData = $this->decodeProvidedData($rawProvidedData);
                $this->save($providedData);
                $this->productsToPush = [];
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function flush(): void
    {
        if (!empty($this->productsToPush)) {
            $rawProvidedData = $this->dataProvider->bulkPush($this->productsToPush);

            $providedData = $this->decodeProvidedData($rawProvidedData);
            $this->save($providedData);
            $this->productsToPush = [];
        }

        var_dump($this->providedData);
    }

    private function decodeProvidedData(string $providedData): array
    {
        $data = json_decode($providedData, true);

        $products = [];
        foreach ($data['_embedded']['product'] as $rawProduct) {
            $product = [];
            $product['id'] = $rawProduct['id'];
            $product['codes'] = $rawProduct['codes'];
            $product['attributes'] = $rawProduct['attributes'];

            $products[] = $product;
        }

        return $products;
    }

    private function save(array $products): void
    {
        $this->providedData = array_merge($this->providedData, $products);
    }
}
